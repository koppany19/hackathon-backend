<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomDailyTaskRequest;
use App\Models\DailyTask;
use App\Models\Subcategory;
use App\Models\Task;
use App\Models\User;
use App\Services\GroupMatchingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyTaskController extends Controller
{
    public function __construct(
        private GroupMatchingService $groupMatchingService,
    ) {}

    public function storeCustom(CreateCustomDailyTaskRequest $request): JsonResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        $subcategory = Subcategory::where('name', $validated['subcategory'])->firstOrFail();

        $dailyTask = DB::transaction(function () use ($user, $validated, $subcategory) {
            // Replace any existing DailyTask for the same category today
            $existing = DailyTask::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->whereHas('task', fn ($q) => $q->where('category', $validated['category']))
                ->first();

            if ($existing) {
                $existing->delete();
            }

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
                'meal'          => 1,
                'sport'         => 2,
                'mental_health' => 3,
                default         => 4,
            })
            ->values();

        return response()->json([
            'date'  => Carbon::today()->toDateString(),
            'tasks' => $dailyTasks,
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $user      = $request->user();
        $today     = strtolower(Carbon::today()->format('l'));
        $todayDate = Carbon::today();

        $user->load(['scheduleItems', 'profile']);

        $joinedTaskIds = DailyTask::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->pluck('task_id');

        // User-created group tasks: same university + overlapping free time with creator + matching category
        $userCreatedTasks = collect();

        if ($user->university_id) {
            $userCreatedTasks = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group_created'))
                ->whereHas('creator', fn ($q) => $q->where('university_id', $user->university_id))
                ->where('is_active', true)
                ->whereDate('created_at', $todayDate)
                ->whereNotIn('id', $joinedTaskIds)
                ->with(['subcategory', 'creator.scheduleItems'])
                ->get()
                ->filter(function ($task) use ($user, $today) {
                    if (!$task->creator) return false;
                    if (!$this->groupMatchingService->hasScheduleOverlap($user, $task->creator, $today)) return false;
                    if (!$this->groupMatchingService->taskTimeInUserFreeWindow($user, $today, $task->time)) return false;
                    return $this->userMatchesTaskCategory($user, $task);
                })
                ->values();
        }

        // AI-generated group tasks targeted at this specific user
        $aiGroupTasks = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group'))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_active', true)
            ->whereDate('created_at', $todayDate)
            ->whereNotIn('id', $joinedTaskIds)
            ->with(['subcategory', 'creator'])
            ->get()
            ->filter(function ($task) use ($user, $today) {
                if (!$this->groupMatchingService->taskTimeInUserFreeWindow($user, $today, $task->time)) return false;
                return $this->userMatchesTaskCategory($user, $task);
            })
            ->values();

        return response()->json($userCreatedTasks->merge($aiGroupTasks)->values());
    }

    private function userMatchesTaskCategory(User $user, Task $task): bool
    {
        $profileKey = match($task->category) {
            'sport'         => 'sports',
            'meal'          => 'food',
            'mental_health' => 'social',
            default         => null,
        };

        if (!$profileKey || !$user->profile) {
            return true;
        }

        $interests = $user->profile->{$profileKey} ?? [];
        return !empty(array_filter((array) $interests));
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

        $this->notifyTaskCreator($task, $user);

        return response()->json($dailyTask->load('task.subcategory'), 201);
    }

    public function groupTasksForUser(Request $request, User $user): JsonResponse
    {
        $today    = strtolower(Carbon::today()->format('l'));
        $todayDate = Carbon::today();

        $user->load('scheduleItems');

        $joinedTaskIds = DailyTask::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->pluck('task_id');

        // AI-generated group tasks where this user is a participant
        $aiGroupTasks = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group'))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_active', true)
            ->whereDate('created_at', $todayDate)
            ->whereNotIn('id', $joinedTaskIds)
            ->with(['subcategory', 'creator'])
            ->get()
            ->filter(fn ($task) => $this->groupMatchingService->taskTimeInUserFreeWindow($user, $today, $task->time))
            ->values();

        // User-created group tasks from the same university with overlapping free time
        $userCreatedTasks = collect();

        if ($user->university_id) {
            $userCreatedTasks = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group_created'))
                ->whereHas('creator', fn ($q) => $q->where('university_id', $user->university_id))
                ->where('is_active', true)
                ->whereDate('created_at', $todayDate)
                ->whereNotIn('id', $joinedTaskIds)
                ->with(['subcategory', 'creator.scheduleItems'])
                ->get()
                ->filter(function ($task) use ($user, $today) {
                    if (!$task->creator) return false;
                    if (!$this->groupMatchingService->hasScheduleOverlap($user, $task->creator, $today)) return false;
                    return $this->groupMatchingService->taskTimeInUserFreeWindow($user, $today, $task->time);
                })
                ->values();
        }

        return response()->json($aiGroupTasks->merge($userCreatedTasks)->values());
    }

    private function notifyTaskCreator(Task $task, \App\Models\User $joiner): void
    {
        // TODO: send push notification to $task->creator when $joiner joins their group task
    }
}
