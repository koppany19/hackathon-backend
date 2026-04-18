<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiGroupTaskService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    private const VALID_DIFFICULTIES = ['easy', 'medium', 'hard'];

    public function generateTask(
        string $category,
        array $sharedInterests,
        string $cityName,
        string $difficulty,
        string $suggestedTimeWindow,
    ): ?array {
        $apiKey = config('services.google.gemini_key');

        if (empty($apiKey)) {
            Log::error('Gemini API key not configured for group task generation.');
            return null;
        }

        $payload = $this->buildPayload($category, $sharedInterests, $cityName, $difficulty, $suggestedTimeWindow);

        try {
            $response = Http::withQueryParameters(['key' => $apiKey])
                ->timeout(30)
                ->post(self::GEMINI_API_URL, $payload);
        } catch (\Exception $e) {
            Log::error('Gemini group task API unreachable', ['error' => $e->getMessage()]);
            return null;
        }

        if (!$response->successful()) {
            Log::error('Gemini group task API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        $rawText = $response->json('candidates.0.content.parts.0.text', '');
        $parsed  = $this->parseJson($rawText);

        if (!$parsed) return null;

        return $this->sanitize($parsed, $category, $difficulty);
    }

    private function buildPayload(
        string $category,
        array $sharedInterests,
        string $cityName,
        string $difficulty,
        string $suggestedTimeWindow,
    ): array {
        $categoryLabel = match ($category) {
            'sport'         => 'physical/sports activity',
            'meal'          => 'food/nutrition',
            'mental_health' => 'social/mental wellness',
            default         => $category,
        };

        $interestsList = implode(', ', $sharedInterests) ?: 'general wellness';

        $prompt = <<<PROMPT
You are a wellness challenge generator for university students. Generate one group task for two students to complete together.

Context:
- Task category: {$categoryLabel} (use exactly "{$category}" in the JSON)
- Shared interests: {$interestsList}
- City: {$cityName}
- Difficulty: {$difficulty} (use exactly this value)
- Suggested time window today: {$suggestedTimeWindow}

Requirements:
- The task must be something two people can do together.
- Make it specific, realistic, and motivating for university students.
- The "time" field: pick a realistic HH:MM start time within the suggested window, or null.
- The "location" field: suggest a real-world place type in {$cityName} fitting the task (e.g. "City Park", "university gym"), or null.
- "difficulty" must be exactly one of: easy, medium, hard.
- "category" must be exactly one of: sport, meal, mental_health.

Return ONLY a minified JSON object — no explanation, no markdown, no backticks:
{"title":"...","description":"...","category":"{$category}","difficulty":"{$difficulty}","time":"HH:MM or null","location":"place or null"}
PROMPT;

        return [
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature'      => 0.8,
                'responseMimeType' => 'application/json',
            ],
        ];
    }

    private function sanitize(array $data, string $category, string $fallbackDifficulty): array
    {
        return [
            'title'       => $data['title'] ?? 'Group wellness challenge',
            'description' => $data['description'] ?? '',
            'category'    => $category,
            'difficulty'  => in_array($data['difficulty'] ?? '', self::VALID_DIFFICULTIES)
                ? $data['difficulty']
                : $fallbackDifficulty,
            'time'        => isset($data['time']) && $data['time'] !== 'null' ? $data['time'] : null,
            'location'    => isset($data['location']) && $data['location'] !== 'null' ? $data['location'] : null,
        ];
    }

    private function parseJson(string $raw): ?array
    {
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $stripped = preg_replace('/\s*```$/', '', $stripped);
        $decoded  = json_decode(trim($stripped), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('Failed to parse Gemini group task response', ['raw' => $raw]);
            return null;
        }

        return $decoded;
    }
}
