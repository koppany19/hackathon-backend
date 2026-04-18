<?php

namespace Database\Seeders;

use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 30 tesztfelhasználó:
 *   - 15 KOMPATIBILIS: Kristóf Botoshoz hasonló profil (vegan, aktív, outdoor, tánc/színház)
 *                      + órarendjük átfed Kristóf szabad ablakaival
 *   - 15 INKOMPATIBILIS: eltérő profil (húsevő, indoor, éjjeli bagoly, gaming stb.)
 *                        + órarendjük kitölti Kristóf szabad ablakait
 *
 * Kristóf szabad ablakai:
 *   Hétfő:     11:50–16:30
 *   Kedd:      11:50–14:30
 *   Szerda:    11:50–13:30, 14:20–16:30
 *   Csütörtök: 09:50–14:30
 *   Péntek:    09:50–12:30
 */
class BulkUserSeeder extends Seeder
{
    public function run(): void
    {
        // ────────────────────────────────────────────────────────────────────
        // KOMPATIBILIS PROFILOK (3 variáció × 5 user = 15)
        // ────────────────────────────────────────────────────────────────────

        $compatibleProfiles = [
            // Variáció A: nagyon hasonló Kristófhoz
            'A' => [
                'sport_frequency' => 'sport_2_3_week',
                'food' => [
                    'dairy_free' => false, 'fast_food' => false, 'fine_dining' => false,
                    'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => false,
                    'kosher' => false, 'lactose_intolerant' => false, 'loves_spicy' => true,
                    'meal_prep' => true, 'nut_allergy' => false, 'street_food' => false,
                    'vegan' => true, 'vegetarian' => false,
                ],
                'sports' => [
                    'badminton' => true, 'basketball' => false, 'cycling' => true,
                    'football' => false, 'gym' => true, 'hiking' => false,
                    'martial_arts' => false, 'running' => true, 'sport_2_3_week' => true,
                    'sport_4_5_week' => false, 'sport_daily' => false, 'sport_once_week' => false,
                    'sport_rarely' => false, 'swimming' => false, 'tennis' => true,
                    'volleyball' => true, 'yoga' => false,
                ],
                'social' => [
                    'art_drawing' => false, 'board_games' => false, 'coffee_culture' => false,
                    'concerts' => false, 'cooking' => false, 'dancing' => true,
                    'early_bird' => false, 'gaming' => false, 'large_groups' => true,
                    'movies_series' => false, 'music' => false, 'night_owl' => false,
                    'photography' => false, 'prefers_indoor' => false, 'prefers_outdoor' => true,
                    'reading' => false, 'small_gatherings' => true, 'theater' => true,
                    'travel' => false, 'volunteering' => false,
                ],
            ],
            // Variáció B: részben hasonló – cycling, running, outdoor, dancing
            'B' => [
                'sport_frequency' => 'sport_4_5_week',
                'food' => [
                    'dairy_free' => false, 'fast_food' => false, 'fine_dining' => false,
                    'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => true,
                    'kosher' => false, 'lactose_intolerant' => false, 'loves_spicy' => true,
                    'meal_prep' => true, 'nut_allergy' => false, 'street_food' => false,
                    'vegan' => false, 'vegetarian' => true,
                ],
                'sports' => [
                    'badminton' => false, 'basketball' => false, 'cycling' => true,
                    'football' => false, 'gym' => true, 'hiking' => true,
                    'martial_arts' => false, 'running' => true, 'sport_2_3_week' => false,
                    'sport_4_5_week' => true, 'sport_daily' => false, 'sport_once_week' => false,
                    'sport_rarely' => false, 'swimming' => false, 'tennis' => true,
                    'volleyball' => false, 'yoga' => false,
                ],
                'social' => [
                    'art_drawing' => false, 'board_games' => false, 'coffee_culture' => false,
                    'concerts' => true, 'cooking' => false, 'dancing' => true,
                    'early_bird' => true, 'gaming' => false, 'large_groups' => true,
                    'movies_series' => false, 'music' => true, 'night_owl' => false,
                    'photography' => false, 'prefers_indoor' => false, 'prefers_outdoor' => true,
                    'reading' => false, 'small_gatherings' => true, 'theater' => false,
                    'travel' => true, 'volunteering' => false,
                ],
            ],
            // Variáció C: sportos, outdoor, meal_prep, volleyball/badminton
            'C' => [
                'sport_frequency' => 'sport_2_3_week',
                'food' => [
                    'dairy_free' => true, 'fast_food' => false, 'fine_dining' => false,
                    'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => false,
                    'kosher' => false, 'lactose_intolerant' => true, 'loves_spicy' => true,
                    'meal_prep' => true, 'nut_allergy' => false, 'street_food' => false,
                    'vegan' => true, 'vegetarian' => false,
                ],
                'sports' => [
                    'badminton' => true, 'basketball' => false, 'cycling' => false,
                    'football' => false, 'gym' => true, 'hiking' => false,
                    'martial_arts' => false, 'running' => true, 'sport_2_3_week' => true,
                    'sport_4_5_week' => false, 'sport_daily' => false, 'sport_once_week' => false,
                    'sport_rarely' => false, 'swimming' => true, 'tennis' => false,
                    'volleyball' => true, 'yoga' => false,
                ],
                'social' => [
                    'art_drawing' => false, 'board_games' => false, 'coffee_culture' => false,
                    'concerts' => false, 'cooking' => false, 'dancing' => true,
                    'early_bird' => false, 'gaming' => false, 'large_groups' => true,
                    'movies_series' => false, 'music' => false, 'night_owl' => false,
                    'photography' => true, 'prefers_indoor' => false, 'prefers_outdoor' => true,
                    'reading' => false, 'small_gatherings' => true, 'theater' => true,
                    'travel' => true, 'volunteering' => true,
                ],
            ],
        ];

        // Kompatibilis órarendek — NEM töltik ki Kristóf szabad ablakait
        $compatibleSchedules = [
            'S1' => [
                ['day' => 'monday',    'start' => '08:00', 'end' => '09:50', 'subject' => 'Matematika e.a.'],
                ['day' => 'monday',    'start' => '10:00', 'end' => '11:50', 'subject' => 'Fizika e.a.'],
                ['day' => 'monday',    'start' => '16:30', 'end' => '18:20', 'subject' => 'Angol nyelv II.'],
                ['day' => 'tuesday',   'start' => '08:00', 'end' => '09:50', 'subject' => 'Programozás gyak.'],
                ['day' => 'tuesday',   'start' => '14:30', 'end' => '16:20', 'subject' => 'Adatbázisok e.a.'],
                ['day' => 'tuesday',   'start' => '16:30', 'end' => '18:20', 'subject' => 'Hálózatok e.a.'],
                ['day' => 'wednesday', 'start' => '08:00', 'end' => '09:50', 'subject' => 'OOP e.a.'],
                ['day' => 'wednesday', 'start' => '10:00', 'end' => '11:50', 'subject' => 'OOP gyak.'],
                ['day' => 'wednesday', 'start' => '16:30', 'end' => '18:20', 'subject' => 'Algoritmusok e.a.'],
                ['day' => 'thursday',  'start' => '08:00', 'end' => '09:50', 'subject' => 'Lineáris algebra e.a.'],
                ['day' => 'thursday',  'start' => '14:30', 'end' => '16:20', 'subject' => 'Rendszerek e.a.'],
                ['day' => 'thursday',  'start' => '16:30', 'end' => '18:20', 'subject' => 'Szoftvertechnológia'],
                ['day' => 'friday',    'start' => '12:30', 'end' => '14:20', 'subject' => 'Pedagógia szem.'],
                ['day' => 'friday',    'start' => '14:30', 'end' => '16:20', 'subject' => 'Testnevelés II.'],
            ],
            'S2' => [
                ['day' => 'monday',    'start' => '08:00', 'end' => '09:50', 'subject' => 'Gépi tanulás e.a.'],
                ['day' => 'monday',    'start' => '10:00', 'end' => '11:50', 'subject' => 'Statisztika gyak.'],
                ['day' => 'monday',    'start' => '18:30', 'end' => '19:20', 'subject' => 'Testnevelés I.'],
                ['day' => 'tuesday',   'start' => '08:00', 'end' => '09:50', 'subject' => 'Valószínűségszámítás'],
                ['day' => 'tuesday',   'start' => '16:30', 'end' => '18:20', 'subject' => 'Mesterséges int. gyak.'],
                ['day' => 'wednesday', 'start' => '08:00', 'end' => '09:50', 'subject' => 'Számítógépes grafika'],
                ['day' => 'wednesday', 'start' => '16:30', 'end' => '18:20', 'subject' => 'Biztonsági rendszerek'],
                ['day' => 'thursday',  'start' => '08:00', 'end' => '09:50', 'subject' => 'Fordítóprogramok e.a.'],
                ['day' => 'thursday',  'start' => '14:30', 'end' => '16:20', 'subject' => 'Fordítóprogramok gyak.'],
                ['day' => 'friday',    'start' => '12:30', 'end' => '14:20', 'subject' => 'Numerikus mód. gyak.'],
                ['day' => 'friday',    'start' => '14:30', 'end' => '16:20', 'subject' => 'Numerikus mód. e.a.'],
            ],
            'S3' => [
                ['day' => 'monday',    'start' => '08:00', 'end' => '08:50', 'subject' => 'Diszkrét matematika'],
                ['day' => 'monday',    'start' => '09:00', 'end' => '09:50', 'subject' => 'Diszkrét mat. gyak.'],
                ['day' => 'monday',    'start' => '10:00', 'end' => '10:50', 'subject' => 'Logika és halmazelmélet'],
                ['day' => 'monday',    'start' => '17:30', 'end' => '19:20', 'subject' => 'Román nyelv II.'],
                ['day' => 'tuesday',   'start' => '08:00', 'end' => '09:50', 'subject' => 'Webfejlesztés e.a.'],
                ['day' => 'tuesday',   'start' => '14:30', 'end' => '16:20', 'subject' => 'Webfejlesztés gyak.'],
                ['day' => 'tuesday',   'start' => '18:30', 'end' => '19:20', 'subject' => 'Testnevelés III.'],
                ['day' => 'wednesday', 'start' => '08:00', 'end' => '09:50', 'subject' => 'Adatbázis rendszerek'],
                ['day' => 'wednesday', 'start' => '10:00', 'end' => '11:50', 'subject' => 'Adatbázis gyak.'],
                ['day' => 'wednesday', 'start' => '17:00', 'end' => '18:50', 'subject' => 'Etika'],
                ['day' => 'thursday',  'start' => '08:00', 'end' => '09:50', 'subject' => 'Számításelmélet'],
                ['day' => 'thursday',  'start' => '14:30', 'end' => '16:20', 'subject' => 'Párhuzamos számítás'],
                ['day' => 'friday',    'start' => '12:30', 'end' => '14:20', 'subject' => 'Projekt labor'],
                ['day' => 'friday',    'start' => '16:00', 'end' => '17:50', 'subject' => 'Szakmai gyakorlat'],
            ],
        ];

        $compatibleUsers = [
            // Variáció A
            ['name' => 'Ádám Fehér',      'email' => 'adam.feher@test.com',      'profile' => 'A', 'schedule' => 'S1', 'uni' => 2, 'city' => 2],
            ['name' => 'Bence Németh',    'email' => 'bence.nemeth@test.com',    'profile' => 'A', 'schedule' => 'S2', 'uni' => 2, 'city' => 2],
            ['name' => 'Csilla Mátyás',   'email' => 'csilla.matyas@test.com',   'profile' => 'A', 'schedule' => 'S3', 'uni' => 2, 'city' => 2],
            ['name' => 'Dóra Lukács',     'email' => 'dora.lukacs@test.com',     'profile' => 'A', 'schedule' => 'S1', 'uni' => 2, 'city' => 2],
            ['name' => 'Erik Sipos',      'email' => 'erik.sipos@test.com',      'profile' => 'A', 'schedule' => 'S2', 'uni' => 2, 'city' => 2],
            // Variáció B
            ['name' => 'Fanni Orosz',     'email' => 'fanni.orosz@test.com',     'profile' => 'B', 'schedule' => 'S3', 'uni' => 2, 'city' => 2],
            ['name' => 'Gergő Bíró',      'email' => 'gergo.biro@test.com',      'profile' => 'B', 'schedule' => 'S1', 'uni' => 2, 'city' => 2],
            ['name' => 'Hanna Vincze',    'email' => 'hanna.vincze@test.com',    'profile' => 'B', 'schedule' => 'S2', 'uni' => 2, 'city' => 2],
            ['name' => 'Imre Takács',     'email' => 'imre.takacs@test.com',     'profile' => 'B', 'schedule' => 'S3', 'uni' => 2, 'city' => 2],
            ['name' => 'Judit Somogyi',   'email' => 'judit.somogyi@test.com',   'profile' => 'B', 'schedule' => 'S1', 'uni' => 2, 'city' => 2],
            // Variáció C
            ['name' => 'Kálmán Rácz',    'email' => 'kalman.racz@test.com',    'profile' => 'C', 'schedule' => 'S2', 'uni' => 2, 'city' => 2],
            ['name' => 'Lilla Fodor',     'email' => 'lilla.fodor@test.com',     'profile' => 'C', 'schedule' => 'S3', 'uni' => 2, 'city' => 2],
            ['name' => 'Márton Jakab',    'email' => 'marton.jakab@test.com',    'profile' => 'C', 'schedule' => 'S1', 'uni' => 2, 'city' => 2],
            ['name' => 'Nóra Vásárhelyi', 'email' => 'nora.vasarhelyi@test.com', 'profile' => 'C', 'schedule' => 'S2', 'uni' => 2, 'city' => 2],
            ['name' => 'Olivér Gál',      'email' => 'oliver.gal@test.com',      'profile' => 'C', 'schedule' => 'S3', 'uni' => 2, 'city' => 2],
        ];

        // ────────────────────────────────────────────────────────────────────
        // INKOMPATIBILIS PROFILOK (3 variáció × 5 user = 15)
        // ────────────────────────────────────────────────────────────────────

        $incompatibleProfiles = [
            // Variáció X: indoor, éjjeli bagoly, gaming, húsevő
            'X' => [
                'sport_frequency' => 'sport_rarely',
                'food' => [
                    'dairy_free' => false, 'fast_food' => true, 'fine_dining' => false,
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
                    'art_drawing' => false, 'board_games' => false, 'coffee_culture' => false,
                    'concerts' => false, 'cooking' => false, 'dancing' => false,
                    'early_bird' => false, 'gaming' => true, 'large_groups' => false,
                    'movies_series' => true, 'music' => false, 'night_owl' => true,
                    'photography' => false, 'prefers_indoor' => true, 'prefers_outdoor' => false,
                    'reading' => false, 'small_gatherings' => false, 'theater' => false,
                    'travel' => false, 'volunteering' => false,
                ],
            ],
            // Variáció Y: olvasás, kávé, fotózás, fine dining
            'Y' => [
                'sport_frequency' => 'sport_once_week',
                'food' => [
                    'dairy_free' => false, 'fast_food' => false, 'fine_dining' => true,
                    'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => true,
                    'kosher' => false, 'lactose_intolerant' => false, 'loves_spicy' => false,
                    'meal_prep' => false, 'nut_allergy' => false, 'street_food' => false,
                    'vegan' => false, 'vegetarian' => false,
                ],
                'sports' => [
                    'badminton' => false, 'basketball' => false, 'cycling' => false,
                    'football' => false, 'gym' => false, 'hiking' => false,
                    'martial_arts' => false, 'running' => false, 'sport_2_3_week' => false,
                    'sport_4_5_week' => false, 'sport_daily' => false, 'sport_once_week' => true,
                    'sport_rarely' => false, 'swimming' => true, 'tennis' => false,
                    'volleyball' => false, 'yoga' => true,
                ],
                'social' => [
                    'art_drawing' => true, 'board_games' => false, 'coffee_culture' => true,
                    'concerts' => false, 'cooking' => true, 'dancing' => false,
                    'early_bird' => true, 'gaming' => false, 'large_groups' => false,
                    'movies_series' => false, 'music' => false, 'night_owl' => false,
                    'photography' => true, 'prefers_indoor' => true, 'prefers_outdoor' => false,
                    'reading' => true, 'small_gatherings' => true, 'theater' => false,
                    'travel' => false, 'volunteering' => false,
                ],
            ],
            // Variáció Z: csapatsportok (foci/kosárlabda), nagy csoportok DE indoor, húsevő
            'Z' => [
                'sport_frequency' => 'sport_daily',
                'food' => [
                    'dairy_free' => false, 'fast_food' => true, 'fine_dining' => false,
                    'gluten_intolerant' => false, 'halal' => false, 'home_cooking' => false,
                    'kosher' => false, 'lactose_intolerant' => false, 'loves_spicy' => false,
                    'meal_prep' => false, 'nut_allergy' => false, 'street_food' => true,
                    'vegan' => false, 'vegetarian' => false,
                ],
                'sports' => [
                    'badminton' => false, 'basketball' => true, 'cycling' => false,
                    'football' => true, 'gym' => false, 'hiking' => false,
                    'martial_arts' => true, 'running' => false, 'sport_2_3_week' => false,
                    'sport_4_5_week' => false, 'sport_daily' => true, 'sport_once_week' => false,
                    'sport_rarely' => false, 'swimming' => false, 'tennis' => false,
                    'volleyball' => false, 'yoga' => false,
                ],
                'social' => [
                    'art_drawing' => false, 'board_games' => true, 'coffee_culture' => false,
                    'concerts' => true, 'cooking' => false, 'dancing' => false,
                    'early_bird' => false, 'gaming' => true, 'large_groups' => true,
                    'movies_series' => false, 'music' => true, 'night_owl' => true,
                    'photography' => false, 'prefers_indoor' => true, 'prefers_outdoor' => false,
                    'reading' => false, 'small_gatherings' => false, 'theater' => false,
                    'travel' => false, 'volunteering' => false,
                ],
            ],
        ];

        // Inkompatibilis órarendek — KITÖLTIK Kristóf szabad ablakait
        $incompatibleSchedules = [
            'D1' => [
                ['day' => 'monday',    'start' => '12:30', 'end' => '14:20', 'subject' => 'Közgazdaságtan e.a.'],
                ['day' => 'monday',    'start' => '14:30', 'end' => '16:20', 'subject' => 'Marketing gyak.'],
                ['day' => 'tuesday',   'start' => '12:30', 'end' => '14:20', 'subject' => 'Pénzügy e.a.'],
                ['day' => 'wednesday', 'start' => '12:30', 'end' => '13:20', 'subject' => 'Számvitel gyak.'],
                ['day' => 'wednesday', 'start' => '14:30', 'end' => '16:20', 'subject' => 'Jog e.a.'],
                ['day' => 'thursday',  'start' => '10:00', 'end' => '11:50', 'subject' => 'Mikroökonómia e.a.'],
                ['day' => 'thursday',  'start' => '12:30', 'end' => '14:20', 'subject' => 'Makroökonómia gyak.'],
                ['day' => 'friday',    'start' => '10:00', 'end' => '11:50', 'subject' => 'Vállalkozástan e.a.'],
            ],
            'D2' => [
                ['day' => 'monday',    'start' => '11:00', 'end' => '12:50', 'subject' => 'Menedzsment e.a.'],
                ['day' => 'monday',    'start' => '13:00', 'end' => '14:50', 'subject' => 'Logisztika gyak.'],
                ['day' => 'tuesday',   'start' => '11:00', 'end' => '12:50', 'subject' => 'Kutatásmódszertan'],
                ['day' => 'wednesday', 'start' => '11:00', 'end' => '13:20', 'subject' => 'Statisztika e.a.'],
                ['day' => 'wednesday', 'start' => '15:00', 'end' => '16:20', 'subject' => 'Számvitel e.a.'],
                ['day' => 'thursday',  'start' => '09:00', 'end' => '10:50', 'subject' => 'HR menedzsment'],
                ['day' => 'thursday',  'start' => '11:00', 'end' => '12:50', 'subject' => 'Controlling e.a.'],
                ['day' => 'thursday',  'start' => '13:00', 'end' => '14:20', 'subject' => 'Pénzügy szem.'],
                ['day' => 'friday',    'start' => '09:00', 'end' => '11:50', 'subject' => 'Gazdasági jog e.a.'],
            ],
            'D3' => [
                ['day' => 'monday',    'start' => '13:00', 'end' => '14:50', 'subject' => 'Szociológia e.a.'],
                ['day' => 'monday',    'start' => '15:00', 'end' => '16:20', 'subject' => 'Pszichológia gyak.'],
                ['day' => 'tuesday',   'start' => '12:00', 'end' => '13:50', 'subject' => 'Kommunikáció e.a.'],
                ['day' => 'wednesday', 'start' => '12:00', 'end' => '13:20', 'subject' => 'Médiaelmélet'],
                ['day' => 'wednesday', 'start' => '14:00', 'end' => '16:20', 'subject' => 'Kultúratudomány'],
                ['day' => 'thursday',  'start' => '10:00', 'end' => '11:50', 'subject' => 'Politikatudomány'],
                ['day' => 'thursday',  'start' => '12:00', 'end' => '14:20', 'subject' => 'Történelem e.a.'],
                ['day' => 'friday',    'start' => '10:30', 'end' => '12:20', 'subject' => 'Irodalomtudomány'],
            ],
        ];

        $incompatibleUsers = [
            // Variáció X
            ['name' => 'Pál Gergely',     'email' => 'pal.gergely@test.com',     'profile' => 'X', 'schedule' => 'D1', 'uni' => 1, 'city' => 1],
            ['name' => 'Réka Halász',     'email' => 'reka.halasz@test.com',     'profile' => 'X', 'schedule' => 'D2', 'uni' => 1, 'city' => 1],
            ['name' => 'Sándor Király',   'email' => 'sandor.kiraly@test.com',   'profile' => 'X', 'schedule' => 'D3', 'uni' => 1, 'city' => 1],
            ['name' => 'Tímea Balogh',    'email' => 'timea.balogh@test.com',    'profile' => 'X', 'schedule' => 'D1', 'uni' => 1, 'city' => 1],
            ['name' => 'Ábel Csizmadia',  'email' => 'abel.csizmadia@test.com',  'profile' => 'X', 'schedule' => 'D2', 'uni' => 1, 'city' => 1],
            // Variáció Y
            ['name' => 'Bernadett Kis',   'email' => 'bernadett.kis@test.com',   'profile' => 'Y', 'schedule' => 'D3', 'uni' => 1, 'city' => 1],
            ['name' => 'Csongor Nagy',    'email' => 'csongor.nagy@test.com',    'profile' => 'Y', 'schedule' => 'D1', 'uni' => 1, 'city' => 1],
            ['name' => 'Diána Szabados',  'email' => 'diana.szabados@test.com',  'profile' => 'Y', 'schedule' => 'D2', 'uni' => 1, 'city' => 1],
            ['name' => 'Elemér Veres',    'email' => 'elemer.veres@test.com',    'profile' => 'Y', 'schedule' => 'D3', 'uni' => 1, 'city' => 1],
            ['name' => 'Flóra Benedek',   'email' => 'flora.benedek@test.com',   'profile' => 'Y', 'schedule' => 'D1', 'uni' => 1, 'city' => 1],
            // Variáció Z
            ['name' => 'Géza Molnár',     'email' => 'geza.molnar@test.com',     'profile' => 'Z', 'schedule' => 'D2', 'uni' => 1, 'city' => 1],
            ['name' => 'Hajnalka Fülöp',  'email' => 'hajnalka.fulop@test.com',  'profile' => 'Z', 'schedule' => 'D3', 'uni' => 1, 'city' => 1],
            ['name' => 'István Barta',    'email' => 'istvan.barta@test.com',    'profile' => 'Z', 'schedule' => 'D1', 'uni' => 1, 'city' => 1],
            ['name' => 'Julianna Péntek', 'email' => 'julianna.pentek@test.com', 'profile' => 'Z', 'schedule' => 'D2', 'uni' => 1, 'city' => 1],
            ['name' => 'Kornél Szűcs',    'email' => 'kornel.szucs@test.com',    'profile' => 'Z', 'schedule' => 'D3', 'uni' => 1, 'city' => 1],
        ];

        // ────────────────────────────────────────────────────────────────────
        // LÉTREHOZÁS
        // ────────────────────────────────────────────────────────────────────

        $allUsers = array_merge(
            array_map(fn($u) => array_merge($u, ['type' => 'compatible']), $compatibleUsers),
            array_map(fn($u) => array_merge($u, ['type' => 'incompatible']), $incompatibleUsers)
        );

        $allEmails = array_column($allUsers, 'email');
        User::whereIn('email', $allEmails)->delete();

        foreach ($allUsers as $userData) {
            $isCompatible = $userData['type'] === 'compatible';
            $profileKey   = $userData['profile'];
            $scheduleKey  = $userData['schedule'];

            $profile  = $isCompatible
                ? $compatibleProfiles[$profileKey]
                : $incompatibleProfiles[$profileKey];

            $schedule = $isCompatible
                ? $compatibleSchedules[$scheduleKey]
                : $incompatibleSchedules[$scheduleKey];

            $user = User::create([
                'name'              => $userData['name'],
                'email'             => $userData['email'],
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'university_id'     => $userData['uni'],
                'city_id'           => $userData['city'],
                'xp'                => rand(0, 500),
                'level'             => rand(1, 3),
                'streak'            => rand(1, 7),
            ]);

            UserProfile::create([
                'user_id'         => $user->id,
                'sport_frequency' => $profile['sport_frequency'],
                'food'            => $profile['food'],
                'sports'          => $profile['sports'],
                'social'          => $profile['social'],
            ]);

            foreach ($schedule as $item) {
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