<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Task;

class TaskPolicy
{
    /**
     * Any authenticated employee can access their task list.
     */
    public function viewAny(Employee $employee): bool
    {
        return true;
    }

    /**
     * A task can be viewed by its assigner or one of its assignees.
     */
    public function view(Employee $employee, Task $task): bool
    {
        return $this->isAssigner($employee, $task)
            || $this->isAssignee($employee, $task);
    }
    
    /**
     * The assigner and assigned employees can view task comments.
     */
    public function viewComments(Employee $employee, Task $task): bool
    {
        return $this->isAssigner($employee, $task) 
            || $this->isAssignee($employee, $task);
    }

    /**
     * Only employees eligible to assign tasks can create them.
     */
    public function create(Employee $employee): bool
    {
        return $employee->canAssignTasks();
    }

    /**
     * Only assigned employees can interact with the task workflow.
     */
    public function start(Employee $employee, Task $task): bool
    {
        return $this->isAssignee($employee, $task);
    }

    public function updateProgress(Employee $employee, Task $task): bool
    {
        return $this->isAssignee($employee, $task);
    }

    public function complete(Employee $employee, Task $task): bool
    {
        return $this->isAssignee($employee, $task);
    }

    /**
     * Only assigned employees can add comments.
     */
    public function comment(Employee $employee, Task $task): bool
    {
        return $this->isAssignee($employee, $task);
    }

    private function isAssignee(Employee $employee, Task $task): bool
    {
        return $task->assignees()
            ->whereKey($employee->getKey())
            ->exists();
    }

    private function isAssigner(Employee $employee, Task $task): bool
    {
        return (string) $task->assigned_by_id === (string) $employee->getKey();
    }
}
