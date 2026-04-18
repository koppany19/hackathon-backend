<?php

namespace App\Http\Controllers;

use App\Services\ImageModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageModerationController extends Controller
{
    public function __construct(private ImageModerationService $moderation) {}

    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png'],
        ]);

        try {
            $result = $this->moderation->analyze($request->file('image'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }

        if (! $result['is_safe']) {
            return response()->json([
                'message' => 'The image contains inappropriate content: '.$result['label'].'.',
            ], 422);
        }

        return response()->json($result);
    }
}
