<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OneSignalService
{
    private const NOTIFICATIONS_PATH = '/notifications';

    public function isConfigured(): bool
    {
        return !empty(config('services.onesignal.app_id'))
            && !empty(config('services.onesignal.rest_api_key'));
    }

    /**
     * Send a push notification to one or more users by external_id alias.
     *
     * @param array<int, string|int> $externalIds
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function sendPushByExternalIds(
        array $externalIds,
        string $title,
        string $message,
        array $data = [],
        array $options = [],
    ): array {
        if (!$this->isConfigured()) {
            throw new RuntimeException('OneSignal is not configured. Set ONESIGNAL_APP_ID and ONESIGNAL_REST_API_KEY.');
        }

        $aliases = array_values(array_unique(array_map('strval', array_filter($externalIds, fn ($id) => $id !== null && $id !== ''))));

        if (empty($aliases)) {
            throw new RuntimeException('No valid external IDs provided for push notification.');
        }

        $payload = [
            'app_id' => config('services.onesignal.app_id'),
            'target_channel' => 'push',
            'include_aliases' => [
                'external_id' => $aliases,
            ],
            'headings' => [
                'en' => $title,
            ],
            'contents' => [
                'en' => $message,
            ],
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        if (!empty($options['url'])) {
            $payload['url'] = $options['url'];
        }

        if (!empty($options['app_url'])) {
            $payload['app_url'] = $options['app_url'];
        }

        if (!empty($options['idempotency_key'])) {
            $payload['idempotency_key'] = $options['idempotency_key'];
        }

        $response = Http::baseUrl((string) config('services.onesignal.base_url', 'https://api.onesignal.com'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => 'Key ' . config('services.onesignal.rest_api_key'),
            ])
            ->timeout(15)
            ->post(self::NOTIFICATIONS_PATH, $payload);

        if (!$response->successful()) {
            Log::error('OneSignal push request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            throw new RuntimeException('OneSignal push request failed with status ' . $response->status() . '.');
        }

        return $response->json() ?? [];
    }

    /**
     * Safe variant for background flows where notification failures should not break business logic.
     *
     * @param array<int, string|int> $externalIds
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    public function trySendPushByExternalIds(
        array $externalIds,
        string $title,
        string $message,
        array $data = [],
        array $options = [],
    ): ?array {
        try {
            return $this->sendPushByExternalIds($externalIds, $title, $message, $data, $options);
        } catch (\Throwable $e) {
            Log::warning('OneSignal push skipped/failed', [
                'error' => $e->getMessage(),
                'external_ids' => $externalIds,
                'title' => $title,
            ]);

            return null;
        }
    }
}
