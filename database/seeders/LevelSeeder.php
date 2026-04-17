<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['level' => 1,  'needed_xp' => 0],
            ['level' => 2,  'needed_xp' => 50],
            ['level' => 3,  'needed_xp' => 120],
            ['level' => 4,  'needed_xp' => 220],
            ['level' => 5,  'needed_xp' => 350],
            ['level' => 6,  'needed_xp' => 520],
            ['level' => 7,  'needed_xp' => 730],
            ['level' => 8,  'needed_xp' => 980],
            ['level' => 9,  'needed_xp' => 1280],
            ['level' => 10, 'needed_xp' => 1630],
            ['level' => 11, 'needed_xp' => 2030],
            ['level' => 12, 'needed_xp' => 2480],
            ['level' => 13, 'needed_xp' => 2980],
            ['level' => 14, 'needed_xp' => 3530],
            ['level' => 15, 'needed_xp' => 4130],
            ['level' => 16, 'needed_xp' => 4780],
            ['level' => 17, 'needed_xp' => 5480],
            ['level' => 18, 'needed_xp' => 6230],
            ['level' => 19, 'needed_xp' => 7030],
            ['level' => 20, 'needed_xp' => 7880],
        ];

        foreach ($levels as $level) {
            DB::table('levels')->insert([
                ...$level,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
