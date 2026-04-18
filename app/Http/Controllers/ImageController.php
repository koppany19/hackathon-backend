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
            Log::warning('Image moderation unavailable, allowing upload through.', [
                'error' => $e->getMessage(),
            ]);
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
        $context = ['user_id' => $request->user()?->id, 'daily_task_id' => $request->input('daily_task_id')];
        Log::info('[uploadTaskImage] START', $context);

        $request->validate([
            'image' => 'required|image',
            'daily_task_id' => 'required|integer|exists:daily_tasks,id',
        ]);

        $file = $request->file('image');
        $user = $request->user();

        Log::info('[uploadTaskImage] file received', array_merge($context, [
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $file->getMimeType(),
            'size_bytes'    => $file->getSize(),
        ]));

        try {
            Log::info('[uploadTaskImage] running moderation', $context);
            $safe = $this->moderation->isSafe($file);
            Log::info('[uploadTaskImage] moderation result', array_merge($context, ['safe' => $safe]));
            if (! $safe) {
                return response()->json(['error' => 'The image contains inappropriate content.'], 422);
            }
        } catch (\RuntimeException $e) {
            Log::warning('[uploadTaskImage] moderation unavailable, allowing through', array_merge($context, [
                'error' => $e->getMessage(),
            ]));
        }

        $dailyTask = DailyTask::with('task.subcategory')
            ->where('id', $request->integer('daily_task_id'))
            ->where('user_id', $user->id)
            ->first();

        if (! $dailyTask) {
            Log::warning('[uploadTaskImage] daily task not found', $context);
            return response()->json(['error' => 'Daily task not found.'], 404);
        }

        Log::info('[uploadTaskImage] daily task found', array_merge($context, [
            'task_id'      => $dailyTask->task_id,
            'category'     => $dailyTask->task?->category,
            'subcategory'  => $dailyTask->task?->subcategory?->name,
            'current_status' => $dailyTask->status,
        ]));

        if ($dailyTask->task?->category === 'meal') {
            Log::info('[uploadTaskImage] meal category — evaluating health', $context);
            try {
                $evaluation = $this->mealHealth->evaluate($file);
                Log::info('[uploadTaskImage] meal evaluation result', array_merge($context, $evaluation));
            } catch (\RuntimeException $e) {
                Log::error('[uploadTaskImage] meal evaluation failed', array_merge($context, [
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ]));
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
            }

            if ($evaluation['health_score'] < 70) {
                Log::info('[uploadTaskImage] meal rejected (score too low)', array_merge($context, $evaluation));
                return response()->json([
                    'error'        => $evaluation['reason'] ?: 'The meal does not appear healthy enough to complete this task.',
                    'health_score' => $evaluation['health_score'],
                ], 422);
            }
        }

        $extension = $file->getClientOriginalExtension();
        $timestamp = (int) (microtime(true) * 1000);
        $randomString = Str::random(8);
        $path = "{$user->id}/{$timestamp}_{$randomString}.{$extension}";

        Log::info('[uploadTaskImage] uploading to supabase-feed', array_merge($context, ['path' => $path]));

        try {
            $uploaded = Storage::disk('supabase-feed')->put($path, file_get_contents($file->getRealPath()), 'public');
            Log::info('[uploadTaskImage] storage put result', array_merge($context, ['uploaded' => $uploaded]));

            if (! $uploaded) {
                Log::error('[uploadTaskImage] storage put returned false', $context);
                return response()->json(['error' => 'Upload failed'], 500);
            }

            $publicUrl = rtrim(env('SUPABASE_URL'), '/').'/storage/v1/object/public/feed-images/'.$path;
            Log::info('[uploadTaskImage] public URL built', array_merge($context, ['url' => $publicUrl]));

            $dailyTask->update(['photo_url' => $publicUrl, 'status' => 'completed']);
            Log::info('[uploadTaskImage] daily task updated to completed', $context);

            $baseXp = $dailyTask->task?->subcategory?->xp_value ?? 0;
            $streak = $user->streak;
            $boostPercent = $streak?->boost ?? 0;
            $earnedXp = (int) round($baseXp * (1 + $boostPercent / 100));

            Log::info('[uploadTaskImage] XP calculation', array_merge($context, [
                'base_xp'       => $baseXp,
                'boost_percent' => $boostPercent,
                'earned_xp'     => $earnedXp,
            ]));

            $user->increment('xp', $earnedXp);
            Log::info('[uploadTaskImage] SUCCESS — XP incremented', $context);

            return response()->json(['publicUrl' => $publicUrl]);
        } catch (\Exception $e) {
            Log::error('[uploadTaskImage] EXCEPTION', array_merge($context, [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]));
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
