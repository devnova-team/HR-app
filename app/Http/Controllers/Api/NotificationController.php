<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Support\ErrorCode;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $service){}

    public function index(Request $request){
        $employee=Auth::user();
        $notifications = $this->service->getMyNotifications($employee,$request);
        return ApiResponse::success($notifications);
    }

    public function markAsRead(string $id)
    {
        $employee = Auth::user();
        $notification = $this->service->getNotification(
            $employee,
            $id
        );
        if (!$notification) {
            return ApiResponse::error(
                ErrorCode::NotFound,
                'Notification not found'
            );
        }
        $this->service->markAsRead($notification);
        return ApiResponse::success([
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead()
    {
        $employee = Auth::user();
        $updatedCount = $this->service->markAllAsRead($employee);
        return ApiResponse::success([
            'message' => 'All notifications marked as read',
            'updated_count' => $updatedCount,
        ]);
    }

}
