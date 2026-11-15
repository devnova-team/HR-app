<?php

namespace App\Repositories\Interfaces;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    /** The employee's record for a work date (company-local), with its breaks. */
    public function findForEmployeeOnDate(string $employeeId, string $workDate): ?AttendanceRecord;

    /**
     * Same as above but locked for update. Only effective inside DB::transaction().
     * Used by break start/end and clock-out to stop concurrent requests.
     */
    public function findForEmployeeOnDateForUpdate(string $employeeId, string $workDate): ?AttendanceRecord;

    public function existsForEmployeeOnDate(string $employeeId, string $workDate): bool;

    public function create(array $data): AttendanceRecord;

    public function update(AttendanceRecord $record, array $data): AttendanceRecord;

    public function createBreak(AttendanceRecord $record, array $data): AttendanceBreak;

    /** The break of this record that has not ended yet, if any. */
    public function findOpenBreak(AttendanceRecord $record): ?AttendanceBreak;

    public function closeBreak(AttendanceBreak $break, CarbonInterface $endedAt): AttendanceBreak;

    /** Completed records (with clock-out) of an employee, newest first. */
    public function paginateCompletedForEmployee(string $employeeId, int $page, int $limit): LengthAwarePaginator;

    /** Records whose work_date falls inside [from, to], with breaks. */
    public function recordsInPeriod(string $employeeId, CarbonInterface $from, CarbonInterface $to): Collection;

    /**
     * One record by id that must belong to this employee.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException (handled as 404)
     */
    public function findForEmployee(string $id, string $employeeId): AttendanceRecord;
}
