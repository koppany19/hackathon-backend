<?php

namespace App\Services;

use App\Models\CreatedTaskParticipant;
use App\Models\Subcategory;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupMatchingService
{
    private const MIN_OVERLAP_MINUTES = 60;
    private const MIN_CATEGORY_SCORE  = 1;
    private const DAY_START           = '07:00';
    private const DAY_END             = '22:00';

    private const CATEGORY_MAP = [
        'sport'         => ['score_key' => 'sport',  'profile_key' => 'sports'],
        'meal'          => ['score_key' => 'food',   'profile_key' => 'food'],
        'mental_health' => ['score_key' => 'social', 'profile_key' => 'social'],
    ];

    public function __construct(
        private MatchingService $matchingService,
        private GeminiGroupTaskService $geminiService,
    ) {}

    public function run(): void
    {
        $today = strtolower(Carbon::today()->format('l'));

        Log::info('[GroupMatching] Starting run', ['day' => $today, 'date' => Carbon::today()->toDateString()]);

        $universityIds = User::whereNotNull('university_id')
            ->has('profile')
            ->distinct()
            ->pluck('university_id');

        Log::info('[GroupMatching] Universities to process', ['count' => $universityIds->count(), 'ids' => $universityIds->toArray()]);

        if ($universityIds->isEmpty()) {
            Log::warning('[GroupMatching] No universities found — no users have a university_id set with a profile.');
            return;
        }

        foreach ($universityIds as $universityId) {
            $users = User::with(['profile', 'scheduleItems', 'city'])
                ->where('university_id', $universityId)
                ->has('profile')
                ->get();

            Log::info('[GroupMatching] University users loaded', [
                'university_id' => $universityId,
                'user_count'    => $users->count(),
                'user_ids'      => $users->pluck('id')->toArray(),
            ]);

            if ($users->count() < 2) {
                Log::info('[GroupMatching] Skipping university — fewer than 2 users with profiles', ['university_id' => $universityId]);
                continue;
            }

            $this->processUniversityGroup($users, $today);
        }

        Log::info('[GroupMatching] Run complete');
    }

    private function processUniversityGroup(Collection $users, string $today): void
    {
        $count = $users->count();

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $this->processPair($users[$i], $users[$j], $today);
            }
        }
    }

    private function processPair(User $userA, User $userB, string $today): void
    {
        $pairLabel = "users [{$userA->id},{$userB->id}]";

        if (!$userA->profile || !$userB->profile) {
            Log::info("[GroupMatching] Skipping {$pairLabel} — missing profile");
            return;
        }

        $freeA = $this->computeFreeWindows($userA->scheduleItems->where('day_of_week', $today)->values());
        $freeB = $this->computeFreeWindows($userB->scheduleItems->where('day_of_week', $today)->values());

        Log::info("[GroupMatching] Free windows for {$pairLabel}", [
            'day'        => $today,
            "user_{$userA->id}_free" => $freeA,
            "user_{$userB->id}_free" => $freeB,
        ]);

        $overlapWindow = $this->findFreeTimeOverlapFromWindows($freeA, $freeB);

        if (!$overlapWindow) {
            Log::info("[GroupMatching] Skipping {$pairLabel} — no free time overlap >= " . self::MIN_OVERLAP_MINUTES . " min on {$today}");
            return;
        }

        Log::info("[GroupMatching] Overlap found for {$pairLabel}", $overlapWindow);

        $score      = $this->matchingService->calculateScore($userA->profile, $userB->profile);
        $cityName   = $userA->city?->name ?? $userB->city?->name ?? 'your city';
        $timeWindow = $overlapWindow['start'] . '-' . $overlapWindow['end'];
        $difficulty = $this->computeDifficulty($userA, $userB, $today);

        Log::info("[GroupMatching] Scores for {$pairLabel}", array_merge($score, [
            'city'       => $cityName,
            'time_window' => $timeWindow,
            'difficulty' => $difficulty,
        ]));

        foreach (self::CATEGORY_MAP as $category => ['score_key' => $scoreKey, 'profile_key' => $profileKey]) {
            if ($score[$scoreKey] < self::MIN_CATEGORY_SCORE) {
                Log::info("[GroupMatching] Skipping category '{$category}' for {$pairLabel} — score {$score[$scoreKey]} < " . self::MIN_CATEGORY_SCORE);
                continue;
            }

            if ($category === 'meal' && $this->matchingService->hasFoodConflict(
                $userA->profile->food,
                $userB->profile->food,
            )) {
                Log::info("[GroupMatching] Skipping 'meal' for {$pairLabel} — food restriction conflict");
                continue;
            }

            if ($this->groupTaskExistsForPair($userA->id, $userB->id, $category)) {
                Log::info("[GroupMatching] Skipping '{$category}' for {$pairLabel} — group task already exists today");
                continue;
            }

            $sharedInterests = $this->getSharedInterests(
                $userA->profile->{$profileKey} ?? [],
                $userB->profile->{$profileKey} ?? [],
            );

            Log::info("[GroupMatching] Generating '{$category}' task for {$pairLabel}", [
                'shared_interests' => $sharedInterests,
            ]);

            $this->generateAndPersistTask(
                $category,
                $sharedInterests,
                $cityName,
                $difficulty,
                $timeWindow,
                [$userA, $userB],
            );
        }
    }

    private function generateAndPersistTask(
        string $category,
        array $sharedInterests,
        string $cityName,
        string $difficulty,
        string $timeWindow,
        array $users,
    ): void {
        Log::info('[GroupMatching] Calling Gemini', [
            'category'  => $category,
            'city'      => $cityName,
            'difficulty' => $difficulty,
            'window'    => $timeWindow,
            'interests' => $sharedInterests,
        ]);

        $taskData = $this->geminiService->generateTask(
            $category,
            $sharedInterests,
            $cityName,
            $difficulty,
            $timeWindow,
        );

        if (!$taskData) {
            Log::error('[GroupMatching] Gemini returned null — skipping task creation');
            return;
        }

        Log::info('[GroupMatching] Gemini response', $taskData);

        $subcategory = Subcategory::where('name', 'group')->first();

        if (!$subcategory) {
            Log::error('[GroupMatching] "group" subcategory not found in DB — run seeders');
            return;
        }

        try {
            DB::transaction(function () use ($taskData, $subcategory, $users) {
                $task = Task::create([
                    'title'              => $taskData['title'],
                    'description'        => $taskData['description'],
                    'category'           => $taskData['category'],
                    'subcategory_id'     => $subcategory->id,
                    'is_active'          => true,
                    'difficulty'         => $taskData['difficulty'],
                    'time'               => $taskData['time'],
                    'location'           => $taskData['location'],
                    'created_by_user_id' => null,
                ]);

                Log::info('[GroupMatching] Task created', ['task_id' => $task->id, 'title' => $task->title]);

                foreach ($users as $user) {
                    CreatedTaskParticipant::create([
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                    ]);
                    Log::info('[GroupMatching] Participant linked', ['task_id' => $task->id, 'user_id' => $user->id]);
                }

                $this->notifyUsers($users, $task);
            });
        } catch (\Throwable $e) {
            Log::error('[GroupMatching] DB transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Free-time overlap
    // -------------------------------------------------------------------------

    public function taskTimeInUserFreeWindow(User $user, string $dayOfWeek, ?string $taskTime): bool
    {
        if (!$taskTime) return true; // no time set — always considered available

        $taskMin  = $this->timeToMinutes($taskTime);
        $freeWindows = $this->computeFreeWindows(
            $user->scheduleItems->where('day_of_week', $dayOfWeek)->values()
        );

        foreach ($freeWindows as $window) {
            if ($taskMin >= $window['start_min'] && $taskMin < $window['end_min']) {
                return true;
            }
        }

        return false;
    }

    public function hasScheduleOverlap(User $userA, User $userB, string $dayOfWeek): bool
    {
        $freeA = $this->computeFreeWindows($userA->scheduleItems->where('day_of_week', $dayOfWeek)->values());
        $freeB = $this->computeFreeWindows($userB->scheduleItems->where('day_of_week', $dayOfWeek)->values());

        return $this->findFreeTimeOverlapFromWindows($freeA, $freeB) !== null;
    }

    private function findFreeTimeOverlap(User $userA, User $userB, string $today): ?array
    {
        $freeA = $this->computeFreeWindows($userA->scheduleItems->where('day_of_week', $today)->values());
        $freeB = $this->computeFreeWindows($userB->scheduleItems->where('day_of_week', $today)->values());

        return $this->findFreeTimeOverlapFromWindows($freeA, $freeB);
    }

    private function findFreeTimeOverlapFromWindows(array $freeA, array $freeB): ?array
    {
        foreach ($freeA as $windowA) {
            foreach ($freeB as $windowB) {
                $overlapStart    = max($windowA['start_min'], $windowB['start_min']);
                $overlapEnd      = min($windowA['end_min'],   $windowB['end_min']);
                $overlapDuration = $overlapEnd - $overlapStart;

                if ($overlapDuration >= self::MIN_OVERLAP_MINUTES) {
                    return [
                        'start'    => $this->minutesToTime($overlapStart),
                        'end'      => $this->minutesToTime($overlapEnd),
                        'duration' => $overlapDuration,
                    ];
                }
            }
        }

        return null;
    }

    private function computeFreeWindows(Collection $scheduleItems): array
    {
        $dayStart = $this->timeToMinutes(self::DAY_START);
        $dayEnd   = $this->timeToMinutes(self::DAY_END);

        if ($scheduleItems->isEmpty()) {
            return [['start_min' => $dayStart, 'end_min' => $dayEnd]];
        }

        $busy = $scheduleItems
            ->map(fn ($item) => [
                'start' => $this->timeToMinutes($item->start_time),
                'end'   => $this->timeToMinutes($item->end_time),
            ])
            ->sortBy('start')
            ->values()
            ->toArray();

        $merged = [];
        foreach ($busy as $block) {
            if (empty($merged)) {
                $merged[] = $block;
                continue;
            }
            $last = &$merged[count($merged) - 1];
            if ($block['start'] <= $last['end']) {
                $last['end'] = max($last['end'], $block['end']);
            } else {
                $merged[] = $block;
            }
        }

        $free   = [];
        $cursor = $dayStart;

        foreach ($merged as $block) {
            if ($block['start'] > $cursor) {
                $free[] = ['start_min' => $cursor, 'end_min' => $block['start']];
            }
            $cursor = max($cursor, $block['end']);
        }

        if ($cursor < $dayEnd) {
            $free[] = ['start_min' => $cursor, 'end_min' => $dayEnd];
        }

        return $free;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function groupTaskExistsForPair(int $userAId, int $userBId, string $category): bool
    {
        $todayTaskIds = Task::whereHas('subcategory', fn ($q) => $q->where('name', 'group'))
            ->where('category', $category)
            ->whereDate('created_at', Carbon::today())
            ->pluck('id');

        if ($todayTaskIds->isEmpty()) return false;

        $userATaskIds = CreatedTaskParticipant::where('user_id', $userAId)
            ->whereIn('task_id', $todayTaskIds)
            ->pluck('task_id');

        if ($userATaskIds->isEmpty()) return false;

        return CreatedTaskParticipant::where('user_id', $userBId)
            ->whereIn('task_id', $userATaskIds)
            ->exists();
    }

    private function getSharedInterests(array $profileA, array $profileB): array
    {
        return array_keys(array_filter(
            $profileA,
            fn ($val, $key) => $val && ($profileB[$key] ?? false),
            ARRAY_FILTER_USE_BOTH,
        ));
    }

    private function computeDifficulty(User $userA, User $userB, string $today): string
    {
        $occupiedMinutes = fn (User $u) => $u->scheduleItems
            ->where('day_of_week', $today)
            ->sum(fn ($item) => $this->timeToMinutes($item->end_time) - $this->timeToMinutes($item->start_time));

        $avg = ($occupiedMinutes($userA) + $occupiedMinutes($userB)) / 2;

        if ($avg > 360) return 'easy';
        if ($avg >= 180) return 'medium';
        return 'hard';
    }

    private function timeToMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }

    private function minutesToTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    private function notifyUsers(array $users, Task $task): void
    {
        // TODO: send push notifications via Expo Push Token once integrated
    }
}
