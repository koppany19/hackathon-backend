<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\SearchController;
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


//search
Route::get('/search/cities', [SearchController::class, 'cities']);
Route::get('/search/universities', [SearchController::class, 'universities']);
