<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyTaskSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();
        $today = Carbon::today()->toDateString();

        foreach ($users as $user) {
            $dayOfWeek = strtolower(Carbon::today()->format('l'));

            $occupiedMinutes = DB::table('schedule_items')
                ->where('user_id', $user->id)
                ->where('day_of_week', $dayOfWeek)
                ->get()
                ->sum(function ($item) {
                    $start = Carbon::parse($item->start_time);
                    $end   = Carbon::parse($item->end_time);
                    return $end->diffInMinutes($start);
                });

            $difficulty = match(true) {
                $occupiedMinutes > 360  => 'easy',
                $occupiedMinutes >= 180 => 'medium',
                default                 => 'hard',
            };

            $categories = ['sport', 'meal', 'mental_health'];

            foreach ($categories as $category) {
                $task = DB::table('tasks')
                    ->join('subcategories', 'tasks.subcategory_id', '=', 'subcategories.id')
                    ->where('tasks.category', $category)
                    ->where('subcategories.name', 'individual')
                    ->where('tasks.difficulty', $difficulty)
                    ->where('tasks.is_active', true)
                    ->inRandomOrder()
                    ->select('tasks.*')
                    ->first();

                if (!$task) {
                    $task = DB::table('tasks')
                        ->join('subcategories', 'tasks.subcategory_id', '=', 'subcategories.id')
                        ->where('tasks.category', $category)
                        ->where('subcategories.name', 'individual')
                        ->where('tasks.is_active', true)
                        ->inRandomOrder()
                        ->select('tasks.*')
                        ->first();
                }

                if (!$task) continue;

                $alreadyExists = DB::table('daily_tasks')
                    ->where('user_id', $user->id)
                    ->where('date', $today)
                    ->where('task_id', $task->id)
                    ->exists();

                if ($alreadyExists) continue;

                DB::table('daily_tasks')->insert([
                    'user_id'    => $user->id,
                    'task_id'    => $task->id,
                    'date'       => $today,
                    'status'     => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
