<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Services\ImageModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function __construct(private ImageModerationService $moderation) {}

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
            Storage::disk('supabase-profiles')->delete($path);

            $uploaded = Storage::disk('supabase-profiles')->put($path, file_get_contents($file->getRealPath()), 'public');

            if (! $uploaded) {
                return response()->json(['error' => 'Upload failed'], 500);
            }

            $publicUrl = rtrim(env('SUPABASE_URL'), '/').'/storage/v1/object/public/profile-images/'.$path;

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

        try {
            if (! $this->moderation->isSafe($file)) {
                return response()->json(['error' => 'The image contains inappropriate content.'], 422);
            }
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }

        $user = $request->user();
        $extension = $file->getClientOriginalExtension();
        $timestamp = (int) (microtime(true) * 1000);
        $randomString = Str::random(8);
        $path = "{$user->id}/{$timestamp}_{$randomString}.{$extension}";

        try {
            $uploaded = Storage::disk('supabase-feed')->put($path, file_get_contents($file->getRealPath()), 'public');

            if (! $uploaded) {
                return response()->json(['error' => 'Upload failed'], 500);
            }

            $publicUrl = rtrim(env('SUPABASE_URL'), '/').'/storage/v1/object/public/feed-images/'.$path;

            DailyTask::where('id', $request->integer('daily_task_id'))
                ->where('user_id', $user->id)
                ->update(['photo_url' => $publicUrl, 'status' => 'completed']);

            return response()->json(['publicUrl' => $publicUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
