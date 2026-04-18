<?php

namespace App\Http\Controllers;

use App\Services\OneSignalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct(
        private OneSignalService $oneSignalService,
    ) {}

    public function sendToMe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:2000',
            'data' => 'sometimes|array',
            'url' => 'sometimes|nullable|url',
            'app_url' => 'sometimes|nullable|string|max:512',
            'idempotency_key' => 'sometimes|nullable|uuid',
        ]);

        $result = $this->oneSignalService->sendPushByExternalIds(
            externalIds: [(string) $request->user()->id],
            title: $validated['title'],
            message: $validated['message'],
            data: $validated['data'] ?? [],
            options: [
                'url' => $validated['url'] ?? null,
                'app_url' => $validated['app_url'] ?? null,
                'idempotency_key' => $validated['idempotency_key'] ?? null,
            ],
        );

        return response()->json([
            'message' => 'Push request accepted by OneSignal.',
            'onesignal' => $result,
        ]);
    }
}
