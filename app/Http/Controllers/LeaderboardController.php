<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::orderByDesc('level')
            ->orderByDesc('xp')
            ->orderByDesc('name')
            ->get(['id', 'name', 'level', 'xp', 'profile_image']);

        return response()->json($users);
    }
}
