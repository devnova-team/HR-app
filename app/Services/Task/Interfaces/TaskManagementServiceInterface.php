<?php

namespace App\Services\Task\Interfaces;

use App\Models\Employee;
use App\Models\Task;

interface TaskManagementServiceInterface
{
    // Create a task, assign eligible employees, and handle its attachments.
    public function createTask(
        Employee $employee,
        array $data
    ): Task;
}
