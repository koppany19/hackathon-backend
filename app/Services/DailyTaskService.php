<?php

namespace App\Services;

use App\Models\DailyTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class DailyTaskService
{
    public function __construct(
        private MatchingService $matchingService
    ) {}

    public function generateForUser(User $user): void
    {
        $today = Carbon::today();

        $alreadyGenerated = DailyTask::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        if ($alreadyGenerated) return;

        $difficulty   = $this->getDifficulty($user, $today);
        $matches      = $this->matchingService->findMatches($user);
        $bestMatch    = $matches[0] ?? null;
        $taskType     = $bestMatch
            ? $this->matchingService->getTaskType([
                'total'  => $bestMatch['score'],
                'sport'  => $bestMatch['sport_score'],
                'food'   => $bestMatch['food_score'],
                'social' => $bestMatch['social_score'],
            ])
            : 'individual';

        $categories = ['sport', 'meal', 'mental_health'];

        foreach ($categories as $category) {
            $effectiveTaskType = $taskType;

            if ($category === 'meal' && $bestMatch) {
                $hasFoodConflict = $this->matchingService->hasFoodConflict(
                    $user->profile?->food,
                    $bestMatch['user']->profile?->food
                );
                if ($hasFoodConflict) {
                    $effectiveTaskType = 'individual';
                }
            }

            $subcategory = $this->getSubcategory($effectiveTaskType);

            $task = Task::where('category', $category)
                ->where('difficulty', $difficulty)
                ->whereHas('subcategory', fn ($q) => $q->where('name', $subcategory))
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();

            if (!$task) {
                $task = Task::where('category', $category)
                    ->where('difficulty', $difficulty)
                    ->whereHas('subcategory', fn ($q) => $q->where('name', 'individual'))
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->first();
            }

            if (!$task) continue;

            DailyTask::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'date'    => $today,
                'status'  => 'pending',
            ]);
        }
    }

    public function generateForAll(): void
    {
        User::with(['profile', 'scheduleItems'])->chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->generateForUser($user);
            }
        });
    }

    private function getDifficulty(User $user, Carbon $date): string
    {
        $dayOfWeek = strtolower($date->format('l'));

        $occupiedMinutes = $user->scheduleItems()
            ->where('day_of_week', $dayOfWeek)
            ->get()
            ->sum(function ($item) {
                $start = Carbon::parse($item->start_time);
                $end   = Carbon::parse($item->end_time);
                return $end->diffInMinutes($start);
            });

        if ($occupiedMinutes > 360) return 'easy';
        if ($occupiedMinutes >= 180) return 'medium';
        return 'hard';
    }

    private function getSubcategory(string $taskType): string
    {
        return $taskType === 'individual' ? 'individual' : 'group';
    }
}
