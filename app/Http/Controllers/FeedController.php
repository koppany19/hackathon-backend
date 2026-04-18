<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\JsonResponse;

class FeedController extends Controller
{
    public function index(int $university_id): JsonResponse
    {
        $tasks = DailyTask::where('status', 'completed')
            ->whereNotNull('photo_url')
            ->with(['user:id,name,university_id,profile_image'])
            ->get();

        [$same, $other] = $tasks->partition(
            fn($dt) => $dt->user?->university_id === $university_id
        );

        $format = fn($dt) => [
            'photo_url'     => $dt->photo_url,
            'user_name'     => $dt->user?->name,
            'university_id' => $dt->user?->university_id,
            'profile_image' => $dt->user?->profile_image,
        ];

        return response()->json([
            'same_university'    => $same->map($format)->values(),
            'other_universities' => $other->map($format)->values(),
        ]);
    }
}
