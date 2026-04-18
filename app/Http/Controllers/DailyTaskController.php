<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomDailyTaskRequest;
use App\Models\CreatedTaskParticipant;
use App\Models\DailyTask;
use App\Models\Subcategory;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
                'is_active'          => false,
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

            foreach ($validated['invited_user_ids'] ?? [] as $invitedUserId) {
                CreatedTaskParticipant::create([
                    'task_id' => $task->id,
                    'user_id' => $invitedUserId,
                ]);

                DailyTask::create([
                    'user_id' => $invitedUserId,
                    'task_id' => $task->id,
                    'date'    => Carbon::today(),
                    'status'  => 'pending',
                ]);
            }

            return $creatorTask;
        });

        return response()->json($dailyTask->load('task.subcategory'), 201);
    }
}
