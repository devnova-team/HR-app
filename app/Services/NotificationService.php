<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Support\Pagination;

class NotificationService{
    public function __construct(protected NotificationRepositoryInterface $repo){}
    public function send(Employee $employee,string $title,string $body,string $type,string $referenceId):Notification{
        return $this->repo->create(
            $employee,
            [
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'reference_id' => $referenceId,
            ]
            );
    }

    public function getMyNotifications(Employee $employee,Request $request): LengthAwarePaginator {
        $limit = Pagination::limit($request);
        return $this->repo->getByEmployee($employee, $limit);
    }
    public function getNotification(Employee $employee,string $notificationId): ?Notification {
        return $this->repo->findByIdForEmployee($employee, $notificationId);
    }

    public function markAsRead(Notification $notification): bool
    {
        return $this->repo->markAsRead($notification);
    }

    // Mark all notifications as read
    public function markAllAsRead(Employee $employee): int
    {
        return $this->repo->markAllAsRead($employee);
    }
}