<?php

namespace App\Http\Controllers;

use App\Models\ScheduleItem;
use App\Services\GeminiTimetableService;
use App\Services\ImageModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private ImageModerationService $moderation,
        private GeminiTimetableService $gemini,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'day_of_week'  => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'subject_name' => ['required', 'string', 'max:255'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i'],
        ]);

        $item = ScheduleItem::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json($item, 201);
    }

    public function update(Request $request, ScheduleItem $scheduleItem): JsonResponse
    {
        if ($scheduleItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'day_of_week'  => ['sometimes', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'subject_name' => ['sometimes', 'string', 'max:255'],
            'start_time'   => ['sometimes', 'date_format:H:i'],
            'end_time'     => ['sometimes', 'date_format:H:i'],
        ]);

        $scheduleItem->update($validated);

        return response()->json($scheduleItem);
    }

    public function replaceFromImage(Request $request): JsonResponse
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

        $user = $request->user();

        $user->scheduleItems()->delete();

        if (! empty($timetable)) {
            ScheduleItem::insert(array_map(fn($item) => [
                'user_id'      => $user->id,
                'day_of_week'  => $item['day_of_week'],
                'subject_name' => $item['subject_name'],
                'start_time'   => $item['start_time'],
                'end_time'     => $item['end_time'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ], $timetable));
        }

        return response()->json($user->scheduleItems()->get());
    }
}
