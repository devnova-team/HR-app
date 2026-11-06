<?php
namespace App\Repositories;

use App\Models\Employee;
use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationRepositoryInterface{
    public function create(Employee $employee,array $data):Notification{
        return $employee->notifications()->create($data);
    }

    public function getByEmployee(Employee $employee,int $limit):LengthAwarePaginator{
        return $employee->notifications()->latest()->paginate($limit);
    }
    public function findByIdForEmployee(Employee $employee,string $notificationId): ?Notification {
        return $employee->notifications()->find($notificationId);
    }
    public function markAsRead(Notification $notification): bool
    {
        return $notification->update(['is_read' => true]);
    }

    public function markAllAsRead(Employee $employee): int
    {
        return $employee->notifications()->where('is_read', false)
            ->update(['is_read' => true]);
    }
}