<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Kolozsvár',
            'Marosvásárhely',
            'Csíkszereda',
            'Nagyvárad',
            'Bukarest',
            'Temesvár',
            'Brassó',
            'Szeben',
            'Iași',
            'Constanța',
        ];

        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'name'       => $city,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
