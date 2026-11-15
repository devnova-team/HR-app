<?php

namespace App\Exceptions\Attendance;

use App\Support\ApiResponse;
use App\Support\ErrorCode;
use Exception;

/**
 * Base class for every business error of the attendance module.
 *
 * The Service throws one of the children. This class renders itself through the
 * project's ApiResponse::error(), so the JSON shape and the HTTP status always
 * come from the shared ErrorCode enum (same as bootstrap/app.php).
 *
 * Laravel calls an exception's own render() before the callbacks registered in
 * ->withExceptions(), so the generic Throwable callback does not override it.
 */
abstract class AttendanceException extends Exception
{
    public function __construct(
        protected ErrorCode $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function errorCode(): ErrorCode
    {
        return $this->errorCode;
    }

    public function render($request)
    {
        return ApiResponse::error($this->errorCode, $this->getMessage());
    }

    /**
     * Expected business errors are not bugs: do not write them to the log.
     * Returning anything other than false tells Laravel "already handled".
     */
    public function report(): bool
    {
        return true;
    }
}
