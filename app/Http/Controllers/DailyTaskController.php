<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomDailyTaskRequest;
use App\Models\DailyTask;
use App\Models\Subcategory;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyTaskController extends Controller
{
    public function storeCustom(CreateCustomDailyTaskRequest $request): JsonResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        $subcategory = Subcategory::where('name', $validated['subcategory'])->firstOrFail();

        $dailyTask = DB::transaction(function () use ($user, $validated, $subcategory) {
            $task = Task::create([
                'title'              => $validated['title'],
                'description'        => $validated['description'],
                'category'           => $validated['category'],
                'subcategory_id'     => $subcategory->id,
                'is_active'          => true,
                'time'               => $validated['time'] ?? null,
                'location'           => $validated['location'] ?? null,
                'created_by_user_id' => $user->id,
            ]);

            $creatorTask = DailyTask::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'date'    => Carbon::today(),
                'status'  => 'pending',
            ]);

            return $creatorTask;
        });

        return response()->json($dailyTask->load('task.subcategory'), 201);
    }

    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        $dailyTasks = DailyTask::with('task.subcategory')
            ->where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->get()
            ->sortBy(fn($dt) => match($dt->task->category) {
                'meal'           => 1,
                'sport'          => 2,
                'mental_health'  => 3,
                default          => 4,
            })
            ->values();

        return response()->json([
            'date'  => Carbon::today()->toDateString(),
            'tasks' => $dailyTasks,
        ], 200);
    }

    public function available(Request $request): JsonResponse
    {
        $user = $request->user();

        $joinedTaskIds = DailyTask::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->pluck('task_id');

        // User-created group tasks visible to everyone
        $userCreatedTasks = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group_created'))
            ->where('is_active', true)
            ->whereDate('created_at', Carbon::today())
            ->whereNotIn('id', $joinedTaskIds)
            ->with('subcategory', 'creator')
            ->get();

        // AI-generated group tasks targeted at this specific user
        $aiGroupTasks = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group'))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_active', true)
            ->whereDate('created_at', Carbon::today())
            ->whereNotIn('id', $joinedTaskIds)
            ->with('subcategory', 'creator')
            ->get();

        return response()->json($userCreatedTasks->merge($aiGroupTasks)->values());
    }

    public function join(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        $alreadyJoined = DailyTask::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->whereDate('date', Carbon::today())
            ->exists();

        if ($alreadyJoined) {
            return response()->json(['message' => 'Already joined this task today.'], 409);
        }

        $dailyTask = DailyTask::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'date'    => Carbon::today(),
            'status'  => 'pending',
        ]);

        return response()->json($dailyTask->load('task.subcategory'), 201);
    }
}
