<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Services\GeminiMealHealthService;
use App\Services\ImageModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function __construct(
        private ImageModerationService $moderation,
        private GeminiMealHealthService $mealHealth,
    ) {}

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        $file = $request->file('image');

        try {
            if (! $this->moderation->isSafe($file)) {
                return response()->json(['error' => 'The image contains inappropriate content.'], 422);
            }
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }

        $user = $request->user();
        $extension = $file->getClientOriginalExtension();
        $path = "{$user->id}/avatar.{$extension}";

        try {
            // Delete first to achieve upsert:true semantics on Supabase S3
            try {
                Storage::disk('supabase-profiles')->delete($path);
            } catch (\Exception) {
                // File may not exist yet on first upload
            }

            $uploaded = Storage::disk('supabase-profiles')->put($path, file_get_contents($file->getRealPath()), 'public');

            if (! $uploaded) {
                return response()->json(['error' => 'Upload failed'], 500);
            }

            $publicUrl = rtrim(env('SUPABASE_URL'), '/').'/storage/v1/object/public/profile-images/'.$path;

            $user->update(['profile_image' => $publicUrl]);

            return response()->json(['publicUrl' => $publicUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadTaskImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'daily_task_id' => 'required|integer|exists:daily_tasks,id',
        ]);

        $file = $request->file('image');
        $user = $request->user();

        Log::info('uploadTaskImage: start', [
            'user_id'       => $user->id,
            'daily_task_id' => $request->input('daily_task_id'),
            'mime'          => $file?->getMimeType(),
            'size'          => $file?->getSize(),
        ]);

        try {
            if (! $this->moderation->isSafe($file)) {
                Log::warning('uploadTaskImage: image not safe', ['user_id' => $user->id]);
                return response()->json(['error' => 'The image contains inappropriate content.'], 422);
            }
        } catch (\RuntimeException $e) {
            Log::error('uploadTaskImage: moderation error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }

        Log::info('uploadTaskImage: image is safe');

        $dailyTask = DailyTask::with('task.subcategory')
            ->where('id', $request->integer('daily_task_id'))
            ->where('user_id', $user->id)
            ->first();

        if (! $dailyTask) {
            Log::warning('uploadTaskImage: daily task not found', [
                'daily_task_id' => $request->input('daily_task_id'),
                'user_id'       => $user->id,
            ]);
            return response()->json(['error' => 'Daily task not found.'], 404);
        }

        Log::info('uploadTaskImage: daily task found', [
            'task_id'  => $dailyTask->task_id,
            'category' => $dailyTask->task?->category,
        ]);

        if ($dailyTask->task?->category === 'meal') {
            try {
                $healthScore = $this->mealHealth->getHealthScore($file);
                Log::info('uploadTaskImage: Gemini health score', ['score' => $healthScore]);
            } catch (\RuntimeException $e) {
                Log::error('uploadTaskImage: Gemini error', ['error' => $e->getMessage(), 'code' => $e->getCode()]);
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
            }

            if ($healthScore < 70) {
                return response()->json([
                    'error'        => 'The meal does not appear healthy enough to complete this task.',
                    'health_score' => $healthScore,
                ], 422);
            }
        }

        $extension = $file->getClientOriginalExtension();
        $timestamp = (int) (microtime(true) * 1000);
        $randomString = Str::random(8);
        $path = "{$user->id}/{$timestamp}_{$randomString}.{$extension}";

        Log::info('uploadTaskImage: uploading to supabase', ['path' => $path]);

        try {
            $uploaded = Storage::disk('supabase-feed')->put($path, file_get_contents($file->getRealPath()), 'public');

            if (! $uploaded) {
                Log::error('uploadTaskImage: supabase put returned false', ['path' => $path]);
                return response()->json(['error' => 'Upload failed'], 500);
            }

            $publicUrl = rtrim(env('SUPABASE_URL'), '/').'/storage/v1/object/public/feed-images/'.$path;

            $dailyTask->update(['photo_url' => $publicUrl, 'status' => 'completed']);

            $baseXp = $dailyTask->task?->subcategory?->xp_value ?? 0;
            $streak = $user->streak;
            $boostPercent = $streak?->boost ?? 0;
            $earnedXp = (int) round($baseXp * (1 + $boostPercent / 100));

            $user->increment('xp', $earnedXp);

            Log::info('uploadTaskImage: success', ['publicUrl' => $publicUrl, 'xp' => $earnedXp]);

            return response()->json(['publicUrl' => $publicUrl]);
        } catch (\Exception $e) {
            Log::error('uploadTaskImage: unexpected exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
