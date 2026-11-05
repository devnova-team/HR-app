<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Limit to 5 attempts per minute
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:10,1'); // Limit to 10 attempts per minute
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // Limit to 10 attempts per minute
});
