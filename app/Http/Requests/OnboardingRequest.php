<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'google_id'      => 'required|string',
            'email'          => 'required|email',
            'name'           => 'required|string|max:255',

            'university'     => 'nullable|exists:universities,id',
            'city'           => 'nullable|exists:cities,id',

            'sport_frequency' => 'nullable|in:daily,4_5_week,2_3_week,once_week,rarely',

            'food'                        => 'nullable|array',
            'food.lactose_intolerant'     => 'boolean',
            'food.gluten_intolerant'      => 'boolean',
            'food.vegan'                  => 'boolean',
            'food.vegetarian'             => 'boolean',
            'food.nut_allergy'            => 'boolean',
            'food.halal'                  => 'boolean',
            'food.kosher'                 => 'boolean',
            'food.dairy_free'             => 'boolean',
            'food.loves_spicy'            => 'boolean',
            'food.meal_prep'              => 'boolean',
            'food.street_food'            => 'boolean',
            'food.fine_dining'            => 'boolean',
            'food.fast_food'              => 'boolean',
            'food.home_cooking'           => 'boolean',

            'sports'                      => 'nullable|array',
            'sports.sport_daily'          => 'boolean',
            'sports.sport_4_5_week'       => 'boolean',
            'sports.sport_2_3_week'       => 'boolean',
            'sports.sport_once_week'      => 'boolean',
            'sports.sport_rarely'         => 'boolean',
            'sports.football'             => 'boolean',
            'sports.basketball'           => 'boolean',
            'sports.running'              => 'boolean',
            'sports.cycling'              => 'boolean',
            'sports.swimming'             => 'boolean',
            'sports.tennis'               => 'boolean',
            'sports.gym'                  => 'boolean',
            'sports.yoga'                 => 'boolean',
            'sports.hiking'               => 'boolean',
            'sports.martial_arts'         => 'boolean',
            'sports.volleyball'           => 'boolean',
            'sports.badminton'            => 'boolean',

            'social'                      => 'nullable|array',
            'social.reading'              => 'boolean',
            'social.movies_series'        => 'boolean',
            'social.gaming'               => 'boolean',
            'social.music'                => 'boolean',
            'social.art_drawing'          => 'boolean',
            'social.photography'          => 'boolean',
            'social.cooking'              => 'boolean',
            'social.travel'               => 'boolean',
            'social.concerts'             => 'boolean',
            'social.theater'              => 'boolean',
            'social.dancing'              => 'boolean',
            'social.board_games'          => 'boolean',
            'social.volunteering'         => 'boolean',
            'social.coffee_culture'       => 'boolean',
            'social.prefers_indoor'       => 'boolean',
            'social.prefers_outdoor'      => 'boolean',
            'social.large_groups'         => 'boolean',
            'social.small_gatherings'     => 'boolean',
            'social.night_owl'            => 'boolean',
            'social.early_bird'           => 'boolean',

            'schedule'                    => 'nullable|array',
            'schedule.data'               => 'nullable|array',
            'schedule.data.*.day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedule.data.*.subject_name'=> 'nullable|string|max:255',
            'schedule.data.*.start_time'  => 'nullable|date_format:H:i',
            'schedule.data.*.end_time'    => 'nullable|date_format:H:i',
        ];
    }
}
