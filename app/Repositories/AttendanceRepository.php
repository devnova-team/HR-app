<?php

namespace App\Repositories;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function findForEmployeeOnDate(string $employeeId, string $workDate): ?AttendanceRecord
    {
        return AttendanceRecord::with('breaks')
            ->where('employee_id', $employeeId)
            ->where('work_date', $workDate)
            ->first();
    }

    public function findForEmployeeOnDateForUpdate(string $employeeId, string $workDate): ?AttendanceRecord
    {
        return AttendanceRecord::with('breaks')
            ->where('employee_id', $employeeId)
            ->where('work_date', $workDate)
            ->lockForUpdate()
            ->first();
    }

    public function existsForEmployeeOnDate(string $employeeId, string $workDate): bool
    {
        return AttendanceRecord::where('employee_id', $employeeId)
            ->where('work_date', $workDate)
            ->exists();
    }

    public function create(array $data): AttendanceRecord
    {
        return AttendanceRecord::create($data);
    }

    public function update(AttendanceRecord $record, array $data): AttendanceRecord
    {
        $record->update($data);

        return $record;
    }

    public function createBreak(AttendanceRecord $record, array $data): AttendanceBreak
    {
        return $record->breaks()->create($data);
    }

    public function findOpenBreak(AttendanceRecord $record): ?AttendanceBreak
    {
        return $record->breaks()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }

    public function closeBreak(AttendanceBreak $break, CarbonInterface $endedAt): AttendanceBreak
    {
        $break->update(['ended_at' => $endedAt]);

        return $break;
    }

    public function paginateCompletedForEmployee(string $employeeId, int $page, int $limit): LengthAwarePaginator
    {
        return AttendanceRecord::with('breaks')
            ->where('employee_id', $employeeId)
            ->whereNotNull('clock_out_at')
            ->orderByDesc('work_date')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function recordsInPeriod(string $employeeId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return AttendanceRecord::with('breaks')
            ->where('employee_id', $employeeId)
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('work_date')
            ->get();
    }

    public function findForEmployee(string $id, string $employeeId): AttendanceRecord
    {
        return AttendanceRecord::with('breaks')
            ->where('id', $id)
            ->where('employee_id', $employeeId)
            ->firstOrFail();
    }
}
