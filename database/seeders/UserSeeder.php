<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->insertGetId([
            'google_id'       => null,
            'name'            => 'koppany',
            'email'           => 'test@gmail.com',
            'password'        => Hash::make('password123'),
            'profile_image'   => 'https://cboazyrniaruscpstlzf.supabase.co/storage/v1/object/public/profile-images/1/avatar.jpeg',
            'university_id'   => 1,
            'city_id'         => 2,
            'xp'              => 0,
            'level'           => 1,
            'streak'          => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('user_profiles')->insert([
            'user_id'         => $userId,
            'sport_frequency' => '2_3_week',
            'food'            => json_encode([
                'lactose_intolerant' => false,
                'gluten_intolerant'  => true,
                'vegan'              => true,
                'vegetarian'         => false,
                'nut_allergy'        => false,
                'halal'              => false,
                'kosher'             => false,
                'dairy_free'         => false,
                'loves_spicy'        => true,
                'meal_prep'          => true,
                'street_food'        => false,
                'fine_dining'        => false,
                'fast_food'          => false,
                'home_cooking'       => false,
            ]),
            'sports'          => json_encode([
                'sport_daily'     => true,
                'sport_4_5_week'  => false,
                'sport_2_3_week'  => false,
                'sport_once_week' => false,
                'sport_rarely'    => false,
                'football'        => false,
                'basketball'      => true,
                'running'         => false,
                'cycling'         => false,
                'swimming'        => false,
                'tennis'          => false,
                'gym'             => false,
                'yoga'            => false,
                'hiking'          => false,
                'martial_arts'    => false,
                'volleyball'      => false,
                'badminton'       => false,
            ]),
            'social'          => json_encode([
                'reading'          => true,
                'movies_series'    => false,
                'gaming'           => false,
                'music'            => false,
                'art_drawing'      => false,
                'photography'      => false,
                'cooking'          => false,
                'travel'           => false,
                'concerts'         => false,
                'theater'          => true,
                'dancing'          => false,
                'board_games'      => false,
                'volunteering'     => false,
                'coffee_culture'   => false,
                'prefers_indoor'   => true,
                'prefers_outdoor'  => true,
                'large_groups'     => false,
                'small_gatherings' => false,
                'night_owl'        => false,
                'early_bird'       => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
