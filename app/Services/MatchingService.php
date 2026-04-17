<?php

namespace App\Services;

use App\Models\User;

class MatchingService
{
    private array $sportKeys = [
        'football', 'basketball', 'running', 'cycling',
        'swimming', 'tennis', 'gym', 'yoga', 'hiking',
        'martial_arts', 'volleyball', 'badminton',
    ];

    private array $foodKeys = [
        'meal_prep', 'street_food', 'fine_dining',
        'fast_food', 'home_cooking', 'loves_spicy',
    ];

    private array $socialKeys = [
        'reading', 'movies_series', 'gaming', 'music',
        'art_drawing', 'photography', 'cooking', 'travel',
        'concerts', 'theater', 'dancing', 'board_games',
        'volunteering', 'coffee_culture',
    ];

    private array $foodRestrictionKeys = [
        'lactose_intolerant', 'gluten_intolerant', 'vegan',
        'vegetarian', 'nut_allergy', 'halal', 'kosher', 'dairy_free',
    ];

    public function findMatches(User $user, int $limit = 10): array
    {
        $profile = $user->profile;
        if (!$profile) return [];

        $candidates = User::with('profile')
            ->where('id', '!=', $user->id)
            ->where(function ($query) use ($user) {
                $query->where('city_id', $user->city_id)
                    ->orWhere('university_id', $user->university_id);
            })
            ->get();

        $matches = [];

        foreach ($candidates as $candidate) {
            if (!$candidate->profile) continue;

            if ($this->hasFoodConflict($profile->food, $candidate->profile->food)) continue;

            $score = $this->calculateScore($profile, $candidate->profile);

            $matches[] = [
                'user'         => $candidate,
                'score'        => $score['total'],
                'sport_score'  => $score['sport'],
                'food_score'   => $score['food'],
                'social_score' => $score['social'],
            ];
        }

        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    public function calculateScore($profileA, $profileB): array
    {
        $sportScore  = $this->countCommonTrue($profileA->sports ?? [], $profileB->sports ?? [], $this->sportKeys);
        $foodScore   = $this->countCommonTrue($profileA->food ?? [],   $profileB->food ?? [],   $this->foodKeys);
        $socialScore = $this->countCommonTrue($profileA->social ?? [], $profileB->social ?? [], $this->socialKeys);

        return [
            'sport'  => $sportScore,
            'food'   => $foodScore,
            'social' => $socialScore,
            'total'  => $sportScore + $foodScore + $socialScore,
        ];
    }

    private function hasFoodConflict(?array $foodA, ?array $foodB): bool
    {
        if (!$foodA || !$foodB) return false;

        foreach ($this->foodRestrictionKeys as $key) {
            if (($foodA[$key] ?? false) !== ($foodB[$key] ?? false)) {
                return true;
            }
        }

        return false;
    }

    private function countCommonTrue(array $a, array $b, array $keys): int
    {
        return count(array_filter($keys, fn($k) => ($a[$k] ?? false) && ($b[$k] ?? false)));
    }

    public function getTaskType(array $score): string
    {
        if ($score['total'] >= 5) return 'created';
        if ($score['sport'] >= 2) return 'group_sport';
        if ($score['food'] >= 2)  return 'group_meal';
        if ($score['total'] >= 2) return 'group';
        return 'individual';
    }
}
