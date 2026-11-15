<?php
namespace App\Repositories\Interfaces;

use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface{
    public function create(Employee $employee,array $data):Notification;
    public function getByEmployee(Employee $employee,int $limit):LengthAwarePaginator;

    //find notifications it relates to the employee
    public function findByIdForEmployee(Employee $employee,string $notificationId): ?Notification;

    public function markAsRead(Notification $notification): bool;

    public function markAllAsRead(Employee $employee): int;
}