
<?php

use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeePushTokenController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware('throttle:10,1');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:forgot-password');

    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:verify-otp');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:reset-password');

});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth Routes
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::put('auth/password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:5,1');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Push Notifications
    Route::post('/devices/push-token', [EmployeePushTokenController::class, 'store']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);

    // Messages
    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store']);
    Route::patch('/conversations/{conversationId}/read', [MessageController::class, 'markAsRead']);
    // Departments
    Route::get('/departments', [DepartmentController::class, 'index']);
    // Employees
    Route::get('/employees', [EmployeeController::class, 'index']);
    // Meetings
    Route::get('/meetings/today', [MeetingController::class, 'today']);
    // Admin
    Route::prefix('admin')->middleware('role:hr')->group(function () {
        // Lookups
        Route::get('/lookups/positions', [LookupController::class, 'positions']);
        // Locations
        Route::get('/locations/countries', [LocationController::class, 'countries']);
        Route::get('/locations/countries/{countryId}/governorates', [LocationController::class, 'governorates'])->whereUuid('countryId');
        Route::get('/locations/governorates/{governorateId}/cities', [LocationController::class, 'cities'])->whereUuid('governorateId');
    });
});
