<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\OnboardingRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'university_id' => $validated['university_id'] ?? null,
            'city_id'       => $validated['city_id'] ?? null,
        ]);

        UserProfile::create([
            'user_id'         => $user->id,
            'sport_frequency' => $validated['sport_frequency'] ?? null,
            'food'            => $validated['food'] ?? null,
            'sports'          => $validated['sports'] ?? null,
            'social'          => $validated['social'] ?? null,
        ]);

        $scheduleData = $validated['schedule']['data'] ?? $validated['schedule'] ?? [];

        if (!empty($scheduleData)) {
            $scheduleItems = array_map(fn($item) => [
                'user_id'      => $user->id,
                'day_of_week'  => $item['day_of_week'],
                'subject_name' => $item['subject_name'],
                'start_time'   => $item['start_time'],
                'end_time'     => $item['end_time'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ], $scheduleData);

            ScheduleItem::insert($scheduleItems);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Hibás email vagy jelszó.'], 422);
        }

        $user = User::with([
            'profile',
            'university',
            'city',
            'scheduleItems',
        ])->where('email', $validated['email'])->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        $currentLevel = Level::where('level', $user->level)->first();
        $nextLevel    = Level::where('level', $user->level + 1)->first();

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'level' => [
                'current'      => $user->level,
                'current_xp'   => $user->xp,
                'needed_xp'    => $currentLevel?->needed_xp,
                'next_level_xp'=> $nextLevel?->needed_xp,
                'xp_to_next'   => $nextLevel ? $nextLevel->needed_xp - $user->xp : null,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout.',
        ]);
    }

    public function onboarding(OnboardingRequest $request)
    {
        Log::info('Onboarding raw data:', $request->all());

        try {
            $user      = $request->user();
            $validated = $request->validated();

            Log::info('User:', ['id' => $user?->id]);
            Log::info('Validated:', $validated);

            $user->update([
                'university_id' => $validated['university'] ?? null,
                'city_id'       => $validated['city'] ?? null,
            ]);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'sport_frequency' => $validated['sport_frequency'] ?? null,
                    'food'            => $validated['food'] ?? null,
                    'sports'          => $validated['sports'] ?? null,
                    'social'          => $validated['social'] ?? null,
                ]
            );

            $scheduleData = $validated['schedule']['data'] ?? $validated['schedule'] ?? [];

            if (!empty($scheduleData)) {
                $user->scheduleItems()->delete();

                ScheduleItem::insert(array_map(fn($item) => [
                    'user_id'      => $user->id,
                    'day_of_week'  => $item['day_of_week'],
                    'subject_name' => $item['subject_name'],
                    'start_time'   => $item['start_time'],
                    'end_time'     => $item['end_time'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ], $scheduleData));
            }

            $user->load(['profile', 'university', 'city', 'scheduleItems']);

            return response()->json(['user' => $user], 200);

        } catch (\Exception $e) {
            Log::error('Onboarding error:', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function me(Request $request)
    {
        $user = User::with([
            'profile',
            'university',
            'city',
            'scheduleItems',
            'streak'
        ])->find($request->user()->id);

        $currentLevel = Level::where('level', $user->level)->first();
        $nextLevel    = Level::where('level', $user->level + 1)->first();

        return response()->json([
            'user'  => $user,
            'level' => [
                'current'       => $user->level,
                'current_xp'    => $user->xp,
                'needed_xp'     => $currentLevel?->needed_xp,
                'next_level_xp' => $nextLevel?->needed_xp,
                'xp_to_next'    => $nextLevel ? $nextLevel->needed_xp - $user->xp : null,
            ],
        ], 200);
    }
}
