<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiTimetableService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a timetable OCR assistant. Your only job is to extract schedule entries from the image and output raw JSON.

STRICT OUTPUT RULES — you must follow all of these:
- Output ONLY the JSON array, nothing else.
- Do NOT include any explanation, commentary, or description.
- Do NOT wrap the output in markdown code fences (no ```json or ```).
- Do NOT include backticks of any kind.
- Do NOT add a trailing newline or any characters after the closing bracket ].
- The very first character of your response must be [ and the very last must be ].

EXTRACTION RULES:
- Translate days of the week to lowercase English: hétfő=monday, kedd=tuesday, szerda=wednesday, csütörtök=thursday, péntek=friday, szombat=saturday, vasárnap=sunday.
- Format all times as "HH:MM" (24-hour clock).
- If no timetable data is found, return exactly: []

OUTPUT FORMAT (return only this structure, minified, no extra characters):
[{"day_of_week":"monday","subject_name":"Algebra","start_time":"09:00","end_time":"11:00"}]
PROMPT;

    public function extract(UploadedFile $file): array
    {
        $apiKey = config('services.google.gemini_key');

        if (empty($apiKey)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $base64   = base64_encode(file_get_contents($file->getRealPath()));
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
                'temperature'    => 0,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = Http::withQueryParameters(['key' => $apiKey])
                ->timeout(30)
                ->post(self::GEMINI_API_URL, $payload);
        } catch (\Exception $e) {
            Log::error('Gemini API request failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Gemini API is unreachable.');
        }

        if (! $response->successful()) {
            Log::error('Gemini API returned an error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Gemini API returned an error.');
        }

        $rawText  = $response->json('candidates.0.content.parts.0.text', '');
        $timetable = $this->parseJson($rawText);

        if ($timetable === null) {
            Log::error('Failed to parse Gemini timetable response', ['raw' => $rawText]);
            throw new RuntimeException('AI returned an unparseable response.');
        }

        return $timetable;
    }

    private function parseJson(string $raw): ?array
    {
        $cleaned = trim($raw);

        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```\s*$/i', '', $cleaned);

        $cleaned = preg_replace('/`[^`]*`/', '', $cleaned);
        $cleaned = trim($cleaned);

        if (preg_match('/(\[.*\])/s', $cleaned, $matches)) {
            $cleaned = $matches[1];
        }

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}
