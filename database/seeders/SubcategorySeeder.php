<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $subcategories = [
            ['name' => 'individual',         'xp_value' => 5],
            ['name' => 'group',              'xp_value' => 10],
            ['name' => 'individual_created', 'xp_value' => 15],
            ['name' => 'group_created',      'xp_value' => 20],
        ];

        foreach ($subcategories as $subcategory) {
            DB::table('subcategories')->insert([
                ...$subcategory,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
