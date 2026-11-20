<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Attendance\AlreadyClockedInException;
use App\Exceptions\Attendance\AlreadyClockedOutException;
use App\Exceptions\Attendance\AttendanceException;
use App\Exceptions\Attendance\BreakAlreadyStartedException;
use App\Exceptions\Attendance\CompanySettingNotConfiguredException;
use App\Exceptions\Attendance\NoActiveBreakException;
use App\Exceptions\Attendance\NotClockedInException;
use App\Exceptions\Attendance\OutsideClockInAreaException;
use App\Exceptions\Attendance\WorkScheduleNotFoundException;
use App\Support\ErrorCode;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

// Extends the Laravel TestCase because ApiResponse needs the application booted.
class AttendanceExceptionsTest extends TestCase
{
    public static function cases(): array
    {
        return [
            'already clocked in'  => [AlreadyClockedInException::class, ErrorCode::Conflict, 409],
            'not clocked in'      => [NotClockedInException::class, ErrorCode::Conflict, 409],
            'already clocked out' => [AlreadyClockedOutException::class, ErrorCode::Conflict, 409],
            'break already begun' => [BreakAlreadyStartedException::class, ErrorCode::Conflict, 409],
            'no active break'     => [NoActiveBreakException::class, ErrorCode::Conflict, 409],
            'outside area'        => [OutsideClockInAreaException::class, ErrorCode::Forbidden, 403],
            'schedule not found'  => [WorkScheduleNotFoundException::class, ErrorCode::NotFound, 404],
            'settings missing'    => [CompanySettingNotConfiguredException::class, ErrorCode::ServerError, 500],
        ];
    }

    #[DataProvider('cases')]
    public function test_error_code_enum(string $class, ErrorCode $expected, int $status): void
    {
        $e = new $class();

        $this->assertInstanceOf(AttendanceException::class, $e);
        $this->assertSame($expected, $e->errorCode());
    }

    #[DataProvider('cases')]
    public function test_renders_through_api_response(string $class, ErrorCode $expected, int $status): void
    {
        $response = (new $class())->render(new Request());

        $this->assertSame($status, $response->getStatusCode());

        $body = $response->getData(true);

        $this->assertArrayHasKey('error', $body);
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertNotEmpty($body['error']['message']);
    }

    public function test_custom_message_is_used(): void
    {
        $response = (new AlreadyClockedInException('Custom text'))->render(new Request());

        $this->assertSame('Custom text', $response->getData(true)['error']['message']);
    }

    public function test_only_the_configuration_error_is_logged(): void
    {
        $this->assertTrue((new AlreadyClockedInException())->report());
        $this->assertFalse((new CompanySettingNotConfiguredException())->report());
    }
}
