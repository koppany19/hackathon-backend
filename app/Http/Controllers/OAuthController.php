<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    public function googleMobile(Request $request)
    {
        $request->validate(['access_token' => 'required|string']);

        try {
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'access_token' => $request->access_token,
            ]);

            if ($response->failed()) {
                return response()->json(['message' => 'Invalid Google token.'], 401);
            }

            $googleUser = $response->json();

            $isNewUser = !User::where('email', $googleUser['email'])->exists();

            $user = User::updateOrCreate(
                ['email' => $googleUser['email']],
                [
                    'name'              => $googleUser['name'] ?? $googleUser['email'],
                    'google_id'         => $googleUser['sub'],
                    'email_verified_at' => now(),
                    'password'          => null,
                ]
            );

            if ($isNewUser) {
                UserProfile::create(['user_id' => $user->id]);
            }

            $user->load(['profile', 'university', 'city', 'scheduleItems']);

            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'user'         => $user,
                'token'        => $token,
                'needs_onboarding' => $isNewUser,
            ]);

        } catch (\Exception $e) {
            Log::error('Google OAuth failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Authentication failed.'], 401);
        }
    }
}
