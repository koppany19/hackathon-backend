<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageModerationController extends Controller
{
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png'],
        ]);

        $file = $request->file('image');
        $apiKey = config('services.nsfw.key');
        $apiUrl = config('services.nsfw.url');

        try {
            $response = Http::withHeaders(['NSFWKEY' => $apiKey])
                ->attach('image', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                ->post($apiUrl);
        } catch (\Exception $e) {
            return response()->json(['message' => 'External moderation service is unreachable.'], 503);
        }

        if (! $response->successful()) {
            return response()->json(['message' => 'External moderation service returned an error.'], 502);
        }

        $data = $response->json();

        if (($data['status'] ?? null) === 'NOQUOTA') {
            return response()->json(['message' => 'NSFW API quota depleted.'], 503);
        }

        if (($data['status'] ?? null) !== 'OK') {
            return response()->json(['message' => 'Unexpected response from moderation service.'], 502);
        }

        return response()->json([
            'is_safe'    => (bool) ($data['is_safe'] ?? false),
            'confidence' => (float) ($data['confidence'] ?? 0.0),
            'label'      => (string) ($data['label'] ?? ''),
        ]);
    }
}
