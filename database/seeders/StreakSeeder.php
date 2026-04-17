<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StreakSeeder extends Seeder
{
    public function run(): void
    {
        $streaks = [
            ['streak_count' => 1, 'boost' => 5],
            ['streak_count' => 2, 'boost' => 10],
            ['streak_count' => 3, 'boost' => 15],
            ['streak_count' => 4, 'boost' => 20],
            ['streak_count' => 5, 'boost' => 25],
        ];

        foreach ($streaks as $streak) {
            DB::table('streaks')->insert([
                'streak_count' => $streak['streak_count'],
                'boost'        => $streak['boost'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
