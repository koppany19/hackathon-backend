<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimetableController extends Controller
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-lite:generateContent';

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a timetable OCR assistant. Extract all schedule entries from the image and return ONLY a minified JSON array with no explanation, no markdown, and no backticks.

Rules:
- Translate days of the week to lowercase English: hétfő=monday, kedd=tuesday, szerda=wednesday, csütörtök=thursday, péntek=friday, szombat=saturday, vasárnap=sunday.
- Format all times as "HH:MM" (24-hour).
- Return an empty array [] if no timetable data is found.

Required JSON schema (return only the array, nothing else):
[{"day_of_week":"monday","subject_name":"Algebra","start_time":"09:00","end_time":"11:00"}]
PROMPT;

    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png'],
        ]);

        $file = $request->file('image');
        $apiKey = config('services.google.gemini_key');

        if (empty($apiKey)) {
            return response()->json(['message' => 'Gemini API key is not configured.'], 500);
        }

        $base64 = base64_encode(file_get_contents($file->getRealPath()));
        $mimeType = $file->getMimeType() ?: 'image/jpeg';

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => self::SYSTEM_PROMPT],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = Http::withQueryParameters(['key' => $apiKey])
                ->timeout(30)
                ->post(self::GEMINI_API_URL, $payload);
        } catch (\Exception $e) {
            Log::error('Gemini API request failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Gemini API is unreachable.'], 503);
        }

        if (! $response->successful()) {
            Log::error('Gemini API returned an error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json(['message' => 'Gemini API returned an error.'], 502);
        }

        $rawText = $response->json('candidates.0.content.parts.0.text', '');

        $timetable = $this->parseJson($rawText);

        if ($timetable === null) {
            Log::error('Failed to parse Gemini timetable response', ['raw' => $rawText]);

            return response()->json(['message' => 'AI returned an unparseable response.'], 422);
        }

        return response()->json(['data' => $timetable]);
    }

    private function parseJson(string $raw): ?array
    {
        $sanitized = $this->sanitize($raw);

        $decoded = json_decode($sanitized, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function sanitize(string $raw): string
    {
        // Strip markdown code fences: ```json ... ``` or ``` ... ```
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $stripped = preg_replace('/\s*```$/', '', $stripped);

        return trim($stripped);
    }
}
