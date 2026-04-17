<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ImageModerationService
{
    public function analyze(UploadedFile $file): array
    {
        $apiUrl = config('services.nsfw.url');

        try {
            $response = Http::attach('image', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                ->post($apiUrl);
        } catch (\Exception $e) {
            throw new RuntimeException('External moderation service is unreachable.', 503, $e);
        }

        if (! $response->successful()) {
            throw new RuntimeException('External moderation service returned an error.', 502);
        }

        $data = $response->json();

        if (($data['status'] ?? null) === 'NOQUOTA') {
            throw new RuntimeException('NSFW API quota depleted.', 503);
        }

        if (($data['status'] ?? null) !== 'OK') {
            throw new RuntimeException('Unexpected response from moderation service.', 502);
        }

        $result = $data['data'] ?? [];

        return [
            'is_safe'    => ! (bool) ($result['nsfw'] ?? true),
            'confidence' => (float) ($result['confidence'] ?? 0.0),
            'label'      => (string) ($result['classification'] ?? ''),
        ];
    }

    public function isSafe(UploadedFile $file): bool
    {
        return $this->analyze($file)['is_safe'];
    }
}
