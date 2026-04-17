<?php

namespace App\Http\Controllers;

use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                              => 'required|string|max:255',
            'email'                             => 'required|email|string|max:255|unique:users',
            'password'                          => 'required|string|min:8',
            'university_id'                     => 'nullable|exists:universities,id',
            'city_id'                           => 'nullable|exists:cities,id',

            // user_profile
            'sport_frequency'                   => 'nullable|in:never,rarely,sometimes,often,daily',
            'diet_type'                         => 'nullable|in:omnivore,vegetarian,vegan,other',
            'food_habits'                       => 'nullable|array',
            'mental_health_score'               => 'nullable|integer|min:1|max:10',
            'sleep_hours'                       => 'nullable|numeric|min:0|max:24',

            // schedule_items
            'schedule'                          => 'nullable|array',
            'schedule.*.day_of_week'            => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedule.*.subject_name'           => 'required|string|max:255',
            'schedule.*.start_time'             => 'required|date_format:H:i',
            'schedule.*.end_time'               => 'required|date_format:H:i|after:schedule.*.start_time',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'university_id' => $validated['university_id'] ?? null,
            'city_id'       => $validated['city_id'] ?? null,
        ]);

        UserProfile::create([
            'user_id'              => $user->id,
            'sport_frequency'      => $validated['sport_frequency'] ?? null,
            'diet_type'            => $validated['diet_type'] ?? null,
            'food_habits'          => $validated['food_habits'] ?? null,
            'mental_health_score'  => $validated['mental_health_score'] ?? null,
            'sleep_hours'          => $validated['sleep_hours'] ?? null,
        ]);

        if (!empty($validated['schedule'])) {
            $scheduleItems = array_map(fn($item) => [
                'user_id'      => $user->id,
                'day_of_week'  => $item['day_of_week'],
                'subject_name' => $item['subject_name'],
                'start_time'   => $item['start_time'],
                'end_time'     => $item['end_time'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ], $validated['schedule']);

            ScheduleItem::insert($scheduleItems);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Hibás email vagy jelszó.'], 422);
        }

        $user = User::where('email', $validated['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
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
