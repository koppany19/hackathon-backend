<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        // Cities
        $cities = [
            'Kolozsvár',
            'Marosvásárhely',
            'Csíkszereda',
            'Nagyvárad',
        ];

        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'name'       => $city,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $kolozsvar      = DB::table('cities')->where('name', 'Kolozsvár')->value('id');
        $marosvasarhely = DB::table('cities')->where('name', 'Marosvásárhely')->value('id');
        $csikszereda    = DB::table('cities')->where('name', 'Csíkszereda')->value('id');
        $nagyvarad      = DB::table('cities')->where('name', 'Nagyvárad')->value('id');

        // Universities
        $universities = [
            ['name' => 'Sapientia Erdélyi Magyar Tudományegyetem – Kolozsvár',      'city_id' => $kolozsvar],
            ['name' => 'Sapientia Erdélyi Magyar Tudományegyetem – Marosvásárhely', 'city_id' => $marosvasarhely],
            ['name' => 'Sapientia Erdélyi Magyar Tudományegyetem – Csíkszereda',    'city_id' => $csikszereda],
            ['name' => 'Sapientia Erdélyi Magyar Tudományegyetem – Nagyvárad',      'city_id' => $nagyvarad],
            ['name' => 'Babeș-Bolyai Tudományegyetem',                              'city_id' => $kolozsvar],
        ];

        foreach ($universities as $university) {
            DB::table('universities')->insert([
                'name'       => $university['name'],
                'city_id'    => $university['city_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
