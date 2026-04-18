<?php

namespace App\Http\Controllers;

use App\Services\GeminiTimetableService;
use App\Services\ImageModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(
        private ImageModerationService $moderation,
        private GeminiTimetableService $gemini,
    ) {}

    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png'],
        ]);

        $file = $request->file('image');

        try {
            $isSafe = $this->moderation->isSafe($file);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 503);
        }

        if (! $isSafe) {
            return response()->json([
                'message' => 'The uploaded image contains inappropriate content and cannot be processed.',
            ], 422);
        }

        try {
            $timetable = $this->gemini->extract($file);
        } catch (\RuntimeException $e) {
            $status = match ($e->getMessage()) {
                'Gemini API key is not configured.' => 500,
                'Gemini API is unreachable.'        => 503,
                'Gemini API returned an error.'     => 502,
                default                             => 422,
            };

            return response()->json(['message' => $e->getMessage()], $status);
        }

        return response()->json(['data' => $timetable]);
    }
}
