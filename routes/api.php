
<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\EmployeePushTokenController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskWorkflowController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BurnoutController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;


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

    // Profile, Assets, Payroll and Burnout
    Route::middleware(CheckForAnyAbility::class . ':access')->group(function () {

        // Burnout
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
});


// Tasks
Route::prefix('tasks')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::get('/today', [TaskController::class, 'today']);
        Route::get('/summary', [TaskController::class, 'summary']);
        Route::get('/permissions', [TaskController::class, 'permissions']);
        Route::get('/assignable-employees', [TaskController::class, 'assignableEmployees']);

        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);

        // Comments
        Route::prefix('{task}/comments')->group(function () {
            Route::get('/', [TaskCommentController::class, 'index']);
            Route::post('/', [TaskCommentController::class, 'store']);
        });

        // Workflow
        Route::post('/{task}/start', [TaskWorkflowController::class, 'start']);
        Route::patch('/{task}/progress', [TaskWorkflowController::class, 'updateProgress']);
        Route::post('/{task}/complete', [TaskWorkflowController::class, 'complete']);
    });
