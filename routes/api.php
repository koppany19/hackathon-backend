<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ImageModerationController;
use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'api works',
        'status' => 200,
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/google', [OAuthController::class, 'googleMobile']);

Route::post('/moderate/image', [ImageModerationController::class, 'analyze']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/avatar', [ImageController::class, 'uploadAvatar']);
    Route::post('/feed/image', [ImageController::class, 'uploadFeedImage']);
});
