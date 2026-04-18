<?php

namespace Database\Seeders;

use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $testEmails = [
            'balint.peter@test.com', 'csaba.molnar@test.com', 'daniel.kovacs@test.com',
            'eszter.varga@test.com', 'gabor.toth@test.com', 'anna.szabo@test.com',
            'bela.horvath@test.com', 'kata.fekete@test.com', 'norbert.kiss@test.com',
            'reka.papp@test.com',
        ];
        User::whereIn('email', $testEmails)->delete();

        // Koppány szabad ablakai:
        // Hétfő:    14:20–16:30
        // Kedd:     11:50–14:30
        // Csütörtök: 09:50–14:30
        // Péntek:   09:50–12:30

        // ─── 5 HASONLÓ USER ────────────────────────────────────────────────
        // Sapientia Kolozsvár (university_id=1), Marosvásárhely (city_id=2)
        // Hasonló profil: gaming, outdoor, vegetáriánus, foci/úszás/yoga
        // Órarendjük NEM fedi Koppány szabad ablakait

        $similarUsers = [
            [
                'name'  => 'Bálint Péter',
                'email' => 'balint.peter@test.com',
            ],
            [
                'name'  => 'Csaba Molnár',
                'email' => 'csaba.molnar@test.com',
            ],
            [
                'name'  => 'Dániel Kovács',
                'email' => 'daniel.kovacs@test.com',
            ],
            [
                'name'  => 'Eszter Varga',
                'email' => 'eszter.varga@test.com',
            ],
            [
                'name'  => 'Gábor Tóth',
                'email' => 'gabor.toth@test.com',
            ],
        ];

        // Hasonló órarend: osztályok reggel és este, szabad a Koppány-féle ablakokban
        $similarSchedule = [
            ['day' => 'monday',    'start' => '08:00', 'end' => '09:50', 'subject' => 'Matematika e.a.'],
            ['day' => 'monday',    'start' => '10:00', 'end' => '11:50', 'subject' => 'Fizika e.a.'],
            ['day' => 'monday',    'start' => '16:30', 'end' => '18:20', 'subject' => 'Angol nyelv II.'],
            ['day' => 'tuesday',   'start' => '08:00', 'end' => '09:50', 'subject' => 'Programozás gyak.'],
            ['day' => 'tuesday',   'start' => '14:30', 'end' => '16:20', 'subject' => 'Adatbázisok e.a.'],
            ['day' => 'tuesday',   'start' => '16:30', 'end' => '18:20', 'subject' => 'Hálózatok e.a.'],
            ['day' => 'wednesday', 'start' => '08:00', 'end' => '09:50', 'subject' => 'OOP e.a.'],
            ['day' => 'wednesday', 'start' => '10:00', 'end' => '11:50', 'subject' => 'OOP gyak.'],
            ['day' => 'wednesday', 'start' => '14:30', 'end' => '16:20', 'subject' => 'Algoritmusok e.a.'],
            ['day' => 'thursday',  'start' => '08:00', 'end' => '09:50', 'subject' => 'Lineáris algebra e.a.'],
            ['day' => 'thursday',  'start' => '14:30', 'end' => '16:20', 'subject' => 'Rendszerek e.a.'],
            ['day' => 'thursday',  'start' => '16:30', 'end' => '18:20', 'subject' => 'Szoftvertechnológia'],
            ['day' => 'friday',    'start' => '12:30', 'end' => '14:20', 'subject' => 'Pedagógia szem.'],
            ['day' => 'friday',    'start' => '14:30', 'end' => '16:20', 'subject' => 'Testnevelés II.'],
        ];

        $similarProfile = [
            'sport_frequency' => 'sport_2_3_week',
            'food' => [
                'dairy_free' => false, 'fast_food' => false, 'fine_dining' => false,
                'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => true,
                'kosher' => false, 'lactose_intolerant' => true, 'loves_spicy' => true,
                'meal_prep' => true, 'nut_allergy' => false, 'street_food' => false,
                'vegan' => false, 'vegetarian' => true,
            ],
            'sports' => [
                'badminton' => false, 'basketball' => false, 'cycling' => false,
                'football' => true, 'gym' => false, 'hiking' => false,
                'martial_arts' => false, 'running' => false, 'sport_2_3_week' => true,
                'sport_4_5_week' => false, 'sport_daily' => false, 'sport_once_week' => false,
                'sport_rarely' => false, 'swimming' => true, 'tennis' => false,
                'volleyball' => false, 'yoga' => true,
            ],
            'social' => [
                'art_drawing' => false, 'board_games' => true, 'coffee_culture' => false,
                'concerts' => false, 'cooking' => false, 'dancing' => true,
                'early_bird' => true, 'gaming' => true, 'large_groups' => true,
                'movies_series' => false, 'music' => false, 'night_owl' => false,
                'photography' => false, 'prefers_indoor' => false, 'prefers_outdoor' => true,
                'reading' => false, 'small_gatherings' => false, 'theater' => false,
                'travel' => false, 'volunteering' => false,
            ],
        ];

        foreach ($similarUsers as $userData) {
            $user = User::create([
                'name'              => $userData['name'],
                'email'             => $userData['email'],
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'university_id'     => 1,
                'city_id'           => 2,
                'xp'                => rand(0, 200),
                'level'             => 1,
                'streak'            => rand(1, 5),
            ]);

            UserProfile::create([
                'user_id'         => $user->id,
                'sport_frequency' => $similarProfile['sport_frequency'],
                'food'            => $similarProfile['food'],
                'sports'          => $similarProfile['sports'],
                'social'          => $similarProfile['social'],
            ]);

            foreach ($similarSchedule as $item) {
                ScheduleItem::create([
                    'user_id'      => $user->id,
                    'day_of_week'  => $item['day'],
                    'subject_name' => $item['subject'],
                    'start_time'   => $item['start'],
                    'end_time'     => $item['end'],
                ]);
            }
        }

        // ─── 5 KÜLÖNBÖZŐ USER ──────────────────────────────────────────────
        // Órarendjük FEDI Koppány szabad ablakait
        // Eltérő profil: indoor, éjjeli bagoly, húsevő, kávé, olvasás

        $differentUsers = [
            [
                'name'  => 'Anna Szabó',
                'email' => 'anna.szabo@test.com',
            ],
            [
                'name'  => 'Béla Horváth',
                'email' => 'bela.horvath@test.com',
            ],
            [
                'name'  => 'Kata Fekete',
                'email' => 'kata.fekete@test.com',
            ],
            [
                'name'  => 'Norbert Kiss',
                'email' => 'norbert.kiss@test.com',
            ],
            [
                'name'  => 'Réka Papp',
                'email' => 'reka.papp@test.com',
            ],
        ];

        // Különböző órarend: épp Koppány szabad ablakait tölti be
        $differentSchedule = [
            ['day' => 'monday',    'start' => '14:30', 'end' => '16:20', 'subject' => 'Közgazdaságtan e.a.'],
            ['day' => 'monday',    'start' => '16:30', 'end' => '18:20', 'subject' => 'Marketing gyak.'],
            ['day' => 'tuesday',   'start' => '10:00', 'end' => '11:50', 'subject' => 'Pénzügy e.a.'],
            ['day' => 'tuesday',   'start' => '12:30', 'end' => '14:20', 'subject' => 'Számvitel gyak.'],
            ['day' => 'wednesday', 'start' => '08:00', 'end' => '09:50', 'subject' => 'Jog e.a.'],
            ['day' => 'wednesday', 'start' => '10:00', 'end' => '11:50', 'subject' => 'Menedzsment e.a.'],
            ['day' => 'wednesday', 'start' => '14:30', 'end' => '16:20', 'subject' => 'Statisztika gyak.'],
            ['day' => 'wednesday', 'start' => '16:30', 'end' => '18:20', 'subject' => 'Kutatásmódszertan'],
            ['day' => 'thursday',  'start' => '10:00', 'end' => '11:50', 'subject' => 'Mikroökonómia e.a.'],
            ['day' => 'thursday',  'start' => '12:30', 'end' => '14:20', 'subject' => 'Makroökonómia gyak.'],
            ['day' => 'thursday',  'start' => '16:30', 'end' => '18:20', 'subject' => 'Pénzügy szem.'],
            ['day' => 'friday',    'start' => '10:00', 'end' => '11:50', 'subject' => 'Vállalkozástan e.a.'],
            ['day' => 'friday',    'start' => '14:30', 'end' => '16:20', 'subject' => 'Controlling gyak.'],
        ];

        $differentProfile = [
            'sport_frequency' => 'sport_rarely',
            'food' => [
                'dairy_free' => false, 'fast_food' => true, 'fine_dining' => true,
                'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => false,
                'kosher' => false, 'lactose_intolerant' => false, 'loves_spicy' => false,
                'meal_prep' => false, 'nut_allergy' => false, 'street_food' => true,
                'vegan' => false, 'vegetarian' => false,
            ],
            'sports' => [
                'badminton' => false, 'basketball' => false, 'cycling' => false,
                'football' => false, 'gym' => false, 'hiking' => false,
                'martial_arts' => false, 'running' => false, 'sport_2_3_week' => false,
                'sport_4_5_week' => false, 'sport_daily' => false, 'sport_once_week' => false,
                'sport_rarely' => true, 'swimming' => false, 'tennis' => false,
                'volleyball' => false, 'yoga' => false,
            ],
            'social' => [
                'art_drawing' => false, 'board_games' => false, 'coffee_culture' => true,
                'concerts' => false, 'cooking' => false, 'dancing' => false,
                'early_bird' => false, 'gaming' => false, 'large_groups' => false,
                'movies_series' => true, 'music' => false, 'night_owl' => true,
                'photography' => true, 'prefers_indoor' => true, 'prefers_outdoor' => false,
                'reading' => true, 'small_gatherings' => true, 'theater' => false,
                'travel' => false, 'volunteering' => false,
            ],
        ];

        foreach ($differentUsers as $userData) {
            $user = User::create([
                'name'              => $userData['name'],
                'email'             => $userData['email'],
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'university_id'     => 1,
                'city_id'           => 2,
                'xp'                => rand(0, 200),
                'level'             => 1,
                'streak'            => rand(1, 3),
            ]);

            UserProfile::create([
                'user_id'         => $user->id,
                'sport_frequency' => $differentProfile['sport_frequency'],
                'food'            => $differentProfile['food'],
                'sports'          => $differentProfile['sports'],
                'social'          => $differentProfile['social'],
            ]);

            foreach ($differentSchedule as $item) {
                ScheduleItem::create([
                    'user_id'      => $user->id,
                    'day_of_week'  => $item['day'],
                    'subject_name' => $item['subject'],
                    'start_time'   => $item['start'],
                    'end_time'     => $item['end'],
                ]);
            }
        }
    }
}
