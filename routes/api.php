<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BurnoutController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

Route::middleware(['auth:sanctum', CheckForAnyAbility::class.':access'])->group(function () {
    // Burnout (static /tasks/* routes first)
    Route::get('/tasks/burnout', [BurnoutController::class, 'stats']);
    Route::get('/tasks/burnout/details', [BurnoutController::class, 'details']);

    // Profile
    Route::get('/me', [ProfileController::class, 'show']);
    Route::get('/me/personal-data', [ProfileController::class, 'personalData']);
    Route::patch('/me/personal-data', [ProfileController::class, 'updatePersonalData']);
    Route::put('/me/avatar', [ProfileController::class, 'updateAvatar']);

    // Assets
    Route::get('/me/assets', AssetController::class);

    // Payroll
    Route::get('/me/payroll', [PayrollController::class, 'index']);
    Route::get('/me/payroll/{id}', [PayrollController::class, 'show'])->whereUuid('id');
    Route::get('/me/payroll/{id}/pdf', [PayrollController::class, 'pdf'])->whereUuid('id');
});
