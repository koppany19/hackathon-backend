<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ImageModerationController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'api works',
        'status' => 200,
    ]);
});

//auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/google', [OAuthController::class, 'googleMobile']);
//onboarding
Route::post('/onboarding', [AuthController::class, 'onboarding'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');


//search
Route::get('/search/cities', [SearchController::class, 'cities']);
Route::get('/search/universities', [SearchController::class, 'universities']);

//moderation
Route::post('/moderate/image', [ImageModerationController::class, 'analyze']);

//timetable
Route::post('/v1/timetable/extract', [TimetableController::class, 'extract']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/avatar', [ImageController::class, 'uploadAvatar']);
    Route::post('/feed/image', [ImageController::class, 'uploadTaskImage']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::post('/daily-tasks/custom', [DailyTaskController::class, 'storeCustom']);

    Route::post('/schedules/from-image', [ScheduleController::class, 'replaceFromImage']);
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::put('/schedules/{scheduleItem}', [ScheduleController::class, 'update']);
});





