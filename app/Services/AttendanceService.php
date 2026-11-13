<?php

namespace App\Services;

use App\Enums\ClockStatus;
use App\Exceptions\Attendance\AlreadyClockedInException;
use App\Exceptions\Attendance\AlreadyClockedOutException;
use App\Exceptions\Attendance\BreakAlreadyStartedException;
use App\Exceptions\Attendance\NoActiveBreakException;
use App\Exceptions\Attendance\NotClockedInException;
use App\Exceptions\Attendance\OutsideClockInAreaException;
use App\Exceptions\Attendance\WorkScheduleNotFoundException;
use App\Models\AttendanceRecord;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\CompanySettingRepositoryInterface;
use App\Repositories\Interfaces\WorkScheduleRepositoryInterface;
use App\Support\GeoDistance;
use App\Support\PayPeriod;
use App\Support\WorkDuration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Business rules of the attendance module (contract section C: Clock In).
 *
 * - Times always come from the server (now()), never from the request.
 * - "Today" (work_date) is the date in the company's timezone, not UTC.
 * - Every state change runs in a transaction on the locked record, and the
 *   status column is updated in the same transaction.
 */
class AttendanceService
{
    private const SELFIE_DISK = 'public';

    public function __construct(
        private readonly AttendanceRepositoryInterface $attendance,
        private readonly WorkScheduleRepositoryInterface $schedules,
        private readonly CompanySettingRepositoryInterface $settings,
        private readonly AttendancePdfService $pdf,
    ) {
    }

    // ------------------------------------------------------------------ reads

    /** WorkSummary: today's hours, pay-period hours, the period and the clock status. */
    public function summary(Employee $employee): array
    {
        $settings = $this->settings->get();
        $timezone = $settings->timezone;
        $now = now();
        $today = now($timezone)->toDateString();

        $record = $this->attendance->findForEmployeeOnDate($employee->id, $today);

        $period = PayPeriod::getDates($today, (int) $settings->pay_period_start_day, $timezone);
        $periodRecords = $this->attendance->recordsInPeriod($employee->id, $period['start'], $period['end']);

        return [
            'todayHours'     => $record ? WorkDuration::calculate($record, $now) : 0,
            'payPeriodHours' => (int) $periodRecords->sum(fn (AttendanceRecord $r) => WorkDuration::calculate($r, $now)),
            'periodStart'    => $period['start'],
            'periodEnd'      => $period['end'],
            'status'         => $record?->status ?? ClockStatus::NOT_CLOCKED_IN,
        ];
    }

    /** History list: completed days only, newest first. */
    public function records(Employee $employee, int $page, int $limit): LengthAwarePaginator
    {
        return $this->attendance->paginateCompletedForEmployee($employee->id, $page, $limit);
    }

    /** One day; the record must belong to the employee (otherwise 404). */
    public function detail(Employee $employee, string $id): AttendanceRecord
    {
        return $this->attendance->findForEmployee($id, $employee->id);
    }

    /** The employee's schedule for today, or the default one. */
    public function schedule(Employee $employee): WorkSchedule
    {
        $schedule = $this->currentSchedule($employee, $this->settings->get());

        return $schedule ?? throw new WorkScheduleNotFoundException();
    }

    public function checkLocation(float $latitude, float $longitude): bool
    {
        return $this->isInsideArea($this->settings->get(), $latitude, $longitude);
    }

    /** @return array{filename: string, content: string} */
    public function pdf(Employee $employee, string $id): array
    {
        $record = $this->attendance->findForEmployee($id, $employee->id);

        return [
            'filename' => 'attendance-' . $record->work_date->toDateString() . '.pdf',
            'content'  => $this->pdf->render($record, $employee, $this->settings->get()),
        ];
    }

    // ---------------------------------------------------------------- changes

    public function clockIn(Employee $employee, UploadedFile $selfie, float $latitude, float $longitude, ?string $notes): array
    {
        $settings = $this->settings->get();
        $workDate = now($settings->timezone)->toDateString();

        // Cheapest check first, and before any file is stored.
        if ($this->attendance->existsForEmployeeOnDate($employee->id, $workDate)) {
            throw new AlreadyClockedInException();
        }

        // The server checks the location again, even if check-location passed.
        if (! $this->isInsideArea($settings, $latitude, $longitude)) {
            throw new OutsideClockInAreaException();
        }

        $schedule = $this->currentSchedule($employee, $settings);

        $path = $selfie->store('attendance/selfies/' . $employee->id, self::SELFIE_DISK);

        try {
            $this->attendance->create([
                'employee_id'        => $employee->id,
                'work_schedule_id'   => $schedule?->id,
                'work_date'          => $workDate,
                'clock_in_at'        => now(),
                'selfie_url'         => $path, // relative path; the Resource builds the full URL
                'clock_in_latitude'  => $latitude,
                'clock_in_longitude' => $longitude,
                'notes'              => $notes,
                'status'             => ClockStatus::CLOCKED_IN,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Two simultaneous clock-ins: the unique (employee, day) index rejected the second one.
            Storage::disk(self::SELFIE_DISK)->delete($path);

            throw new AlreadyClockedInException();
        } catch (Throwable $e) {
            Storage::disk(self::SELFIE_DISK)->delete($path);

            throw $e;
        }

        return $this->summary($employee);
    }

    public function startBreak(Employee $employee): array
    {
        DB::transaction(function () use ($employee) {
            $record = $this->lockedToday($employee);
            $status = $record?->status;

            match (true) {
                $record === null                     => throw new NotClockedInException(),
                $status === ClockStatus::CLOCKED_OUT => throw new AlreadyClockedOutException(),
                $status === ClockStatus::ON_BREAK    => throw new BreakAlreadyStartedException(),
                default                              => null,
            };

            $this->attendance->createBreak($record, ['started_at' => now()]);
            $this->attendance->update($record, ['status' => ClockStatus::ON_BREAK]);
        });

        return $this->summary($employee);
    }

    public function endBreak(Employee $employee): array
    {
        DB::transaction(function () use ($employee) {
            $record = $this->lockedToday($employee);
            $status = $record?->status;

            match (true) {
                $record === null                     => throw new NotClockedInException(),
                $status === ClockStatus::CLOCKED_OUT => throw new AlreadyClockedOutException(),
                $status !== ClockStatus::ON_BREAK    => throw new NoActiveBreakException(),
                default                              => null,
            };

            $break = $this->attendance->findOpenBreak($record) ?? throw new NoActiveBreakException();

            $this->attendance->closeBreak($break, now());
            $this->attendance->update($record, ['status' => ClockStatus::CLOCKED_IN]);
        });

        return $this->summary($employee);
    }

    public function clockOut(Employee $employee): array
    {
        DB::transaction(function () use ($employee) {
            $record = $this->lockedToday($employee);
            $status = $record?->status;

            match (true) {
                $record === null                     => throw new NotClockedInException(),
                $status === ClockStatus::CLOCKED_OUT => throw new AlreadyClockedOutException(),
                default                              => null,
            };

            $now = now();

            // The contract shows "Clock Out" while on a break: close the break at the same instant.
            if ($status === ClockStatus::ON_BREAK) {
                $openBreak = $this->attendance->findOpenBreak($record);

                if ($openBreak) {
                    $this->attendance->closeBreak($openBreak, $now);
                }
            }

            // Clock-out location is not sent by the app (contract), so those columns stay null.
            $this->attendance->update($record, [
                'clock_out_at' => $now,
                'status'       => ClockStatus::CLOCKED_OUT,
            ]);
        });

        return $this->summary($employee);
    }

    // ---------------------------------------------------------------- helpers

    /** Today's record of the employee, locked for update (call inside a transaction). */
    private function lockedToday(Employee $employee): ?AttendanceRecord
    {
        $today = now($this->settings->get()->timezone)->toDateString();

        return $this->attendance->findForEmployeeOnDateForUpdate($employee->id, $today);
    }

    private function currentSchedule(Employee $employee, CompanySetting $settings): ?WorkSchedule
    {
        $today = now($settings->timezone)->toDateString();

        return $this->schedules->findEffectiveForEmployee($employee->id, $today)
            ?? $this->schedules->findDefault();
    }

    private function isInsideArea(CompanySetting $settings, float $latitude, float $longitude): bool
    {
        return GeoDistance::isWithinRadius(
            $latitude,
            $longitude,
            (float) $settings->latitude,
            (float) $settings->longitude,
            (float) $settings->allowed_radius_meters,
        );
    }
}
