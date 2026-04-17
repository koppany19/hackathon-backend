<?php

namespace App\Http\Controllers;

use App\Models\User;
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

            $user = User::updateOrCreate(
                ['email' => $googleUser['email']],
                [
                    'name' => $googleUser['name'] ?? $googleUser['email'],
                    'google_id' => $googleUser['sub'],
                    'email_verified_at' => now(),
                    'password' => null,
                ]
            );

            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            Log::error('Google OAuth failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Authentication failed.'], 401);
        }
    }
}
