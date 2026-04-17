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
        $request->validate([
            'access_token' => 'required_without:id_token|string|nullable',
            'id_token'     => 'required_without:access_token|string|nullable',
        ]);

        try {
            if ($request->id_token) {
                $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $request->id_token,
                ]);
            } else {
                $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                    'access_token' => $request->access_token,
                ]);
            }

            if ($response->failed()) {
                return response()->json(['message' => 'Invalid Google token.'], 401);
            }

            $googleUser = $response->json();

            Log::info('Google user data:', $googleUser);

            $user = User::where('google_id', $googleUser['sub'])
                ->orWhere('email', $googleUser['email'])
                ->first();

            if (!$user) {
                return response()->json([
                    'message'          => 'User not found. Please register first.',
                    'needs_onboarding' => true,
                    'google_data'      => [
                        'name'      => $googleUser['name'] ?? null,
                        'email'     => $googleUser['email'] ?? null,
                        'google_id' => $googleUser['sub'] ?? null,
                    ],
                ], 404);
            }

            $user->load(['profile', 'university', 'city', 'scheduleItems']);

            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'user'  => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Google OAuth failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
