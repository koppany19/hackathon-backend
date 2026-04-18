<?php

namespace App\Services;

use App\Models\DailyTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class DailyTaskService
{
    private const CATEGORIES = ['sport', 'meal', 'mental_health'];

    public function generateForUser(User $user): void
    {
        $today = Carbon::today();

        $alreadyGenerated = DailyTask::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        if ($alreadyGenerated) return;

        $dayOfWeek = strtolower($today->format('l'));
        $difficulty = $this->getDifficulty($user, $dayOfWeek);

        foreach (self::CATEGORIES as $category) {
            $task = Task::where('category', $category)
                ->where('difficulty', $difficulty)
                ->whereHas('subcategory', fn ($q) => $q->where('name', 'individual'))
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();

            // Fall back to any difficulty if none found for the computed one
            if (!$task) {
                $task = Task::where('category', $category)
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

    private function getDifficulty(User $user, string $dayOfWeek): string
    {
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
}
