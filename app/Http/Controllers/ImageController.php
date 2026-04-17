<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $user = $request->user();
        $file = $request->file('image');
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

    public function uploadFeedImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $user = $request->user();
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $timestamp = (int) (microtime(true) * 1000); // milliseconds like Date.now()
        $randomString = Str::random(8);
        $path = "{$user->id}/{$timestamp}_{$randomString}.{$extension}";

        try {
            $uploaded = Storage::disk('supabase-feed')->put($path, file_get_contents($file->getRealPath()), 'public');

            if (! $uploaded) {
                return response()->json(['error' => 'Upload failed'], 500);
            }

            $publicUrl = rtrim(env('SUPABASE_URL'), '/').'/storage/v1/object/public/feed-images/'.$path;

            return response()->json([
                'publicUrl' => $publicUrl,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
