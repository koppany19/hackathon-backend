<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiMealHealthService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    private const PROMPT = <<<'PROMPT'
You are a nutrition and food health evaluator. Analyze the food visible in the image and estimate how healthy it is on a scale from 0 to 100, where 0 means completely unhealthy (e.g. junk food, highly processed) and 100 means perfectly healthy (e.g. fresh vegetables, balanced whole meal).

Consider factors like: vegetables, fruits, whole grains, lean protein, low sugar, low saturated fat, minimal processing.

Return ONLY a valid JSON object with two fields, nothing else, no markdown, no explanation. If the score is below 70, provide a short reason; otherwise reason can be null:
{"health_score": 75, "reason": null}
PROMPT;

    public function evaluate(UploadedFile $file): array
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
                        ['text' => self::PROMPT],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature'      => 0,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = Http::withQueryParameters(['key' => $apiKey])
                ->timeout(30)
                ->post(self::GEMINI_API_URL, $payload);
        } catch (\Exception $e) {
            Log::error('Gemini meal health API request failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Gemini API is unreachable.');
        }

        if (! $response->successful()) {
            Log::error('Gemini meal health API returned an error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Gemini API returned an error.');
        }

        $rawText = $response->json('candidates.0.content.parts.0.text', '');
        $result  = $this->parseResult($rawText);

        if ($result === null) {
            Log::error('Failed to parse Gemini meal health response', ['raw' => $rawText]);
            throw new RuntimeException('AI returned an unparseable response.');
        }

        return $result;
    }

    public function getHealthScore(UploadedFile $file): int
    {
        return $this->evaluate($file)['health_score'];
    }

    private function parseResult(string $raw): ?array
    {
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $stripped = preg_replace('/\s*```$/', '', $stripped);
        $decoded  = json_decode(trim($stripped), true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        $score = $decoded['health_score'] ?? null;

        if (! is_numeric($score)) {
            return null;
        }

        return [
            'health_score' => (int) max(0, min(100, $score)),
            'reason'       => $decoded['reason'] ?? null,
        ];
    }


}
