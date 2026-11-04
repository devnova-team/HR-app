<?php

namespace App\Enums;

enum TaskStatus: string
{
    case ToDo = 'toDo';
    case InProgress = 'inProgress';
    case Done = 'done';
}
