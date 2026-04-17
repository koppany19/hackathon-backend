<?php

namespace App\Http\Controllers;

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
        \Log::info('Schedule data:', ['schedule' => $request->input('schedule')]);
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
        ])
            ->where('email', $validated['email'])
            ->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout.',
        ]);
    }
}
