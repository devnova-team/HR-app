<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-only: uses the query builder on tasks / task_assignees / sprints / attendance_*
 * so this module does not depend on other developers' models.
 */
class BurnoutService
{
    /** @return array{sprintName: string, level: string, tasksCompleted: int, tasksAverage: int, progress: float} */
    public function stats(Employee $employee): array
    {
        $sprint = $this->currentSprint();

        if ($sprint === null) {
            return [
                'sprintName' => '',
                'level' => 'good',
                'tasksCompleted' => 0,
                'tasksAverage' => 0,
                'progress' => 0.0,
            ];
        }

        $completed = $this->doneAssignments($sprint->id)
            ->where('task_assignees.employee_id', $employee->id)
            ->count();

        $totalDone = $this->doneAssignments($sprint->id)->count();
        $activeEmployees = DB::table('employees')->where('is_active', true)->count();
        $average = (int) round($totalDone / max($activeEmployees, 1));

        return [
            'sprintName' => $sprint->name,
            'level' => $this->levelFor($completed, $average),
            'tasksCompleted' => $completed,
            'tasksAverage' => $average,
            'progress' => $this->progressFor($completed, $average),
        ];
    }

    public function details(Employee $employee): array
    {
        return $this->stats($employee) + [
            'workingLevel' => $this->workingLevel($employee),
            'workingPeriod' => $this->workingPeriod($employee),
        ];
    }

    public function levelFor(int $completed, int $average): string
    {
        if ($completed < (int) config('burnout.min_tasks', 3)) {
            return 'good';
        }

        $ratio = $completed / max($average, 1);

        return match (true) {
            $ratio >= (float) config('burnout.poor_ratio', 2.0) => 'poor',
            $ratio >= (float) config('burnout.warning_ratio', 1.5) => 'warning',
            default => 'good',
        };
    }

    public function progressFor(int $completed, int $average): float
    {
        $ratio = $completed / max($average, 1);

        return round(min(1.0, $ratio / (float) config('burnout.poor_ratio', 2.0)), 2);
    }

    /**
     * Seconds worked in one day = (clock out - clock in) - finished breaks.
     *
     * @param  iterable<array{0: string|\DateTimeInterface, 1: string|\DateTimeInterface|null}>  $breaks  [start, end] pairs
     */
    public static function workedSeconds(
        string|\DateTimeInterface $clockIn,
        string|\DateTimeInterface $clockOut,
        iterable $breaks = [],
    ): int {
        $seconds = Carbon::parse($clockOut)->getTimestamp() - Carbon::parse($clockIn)->getTimestamp();

        foreach ($breaks as [$start, $end]) {
            if ($end !== null) {
                $seconds -= Carbon::parse($end)->getTimestamp() - Carbon::parse($start)->getTimestamp();
            }
        }

        return max($seconds, 0);
    }

    /** Bar chart: story points the employee finished in each of the last 5 sprints (oldest first). */
    private function workingLevel(Employee $employee): array
    {
        $sprints = DB::table('sprints')
            ->where('start_date', '<=', $this->today()->toDateString())
            ->orderByDesc('start_date')
            ->limit(5)
            ->get(['id', 'name'])
            ->reverse()
            ->values();

        if ($sprints->isEmpty()) {
            return [];
        }

        $points = DB::table('tasks')
            ->join('task_assignees', 'task_assignees.task_id', '=', 'tasks.id')
            ->whereIn('tasks.sprint_id', $sprints->pluck('id'))
            ->where('tasks.status', 'done')
            ->where('task_assignees.employee_id', $employee->id)
            ->groupBy('tasks.sprint_id')
            ->selectRaw('tasks.sprint_id as sprint_id, SUM(tasks.story_points) as points')
            ->pluck('points', 'sprint_id');

        return $sprints->map(fn ($s) => [
            'sprintName' => $s->name,
            'storyPoints' => (int) ($points[$s->id] ?? 0),
        ])->all();
    }

    /** Line chart: worked seconds per month for the last 5 months, including empty months. */
    private function workingPeriod(Employee $employee): array
    {
        $firstMonth = $this->today()->copy()->startOfMonth()->subMonths(4);

        $months = collect(range(0, 4))->mapWithKeys(
            fn ($i) => [$firstMonth->copy()->addMonths($i)->format('Y-m') => 0]
        );

        $records = DB::table('attendance_records')
            ->where('employee_id', $employee->id)
            ->where('work_date', '>=', $firstMonth->toDateString())
            ->whereNotNull('clock_out_at')
            ->get(['id', 'work_date', 'clock_in_at', 'clock_out_at']);

        /** @var Collection<string, Collection> $breaks */
        $breaks = DB::table('attendance_breaks')
            ->whereIn('attendance_record_id', $records->pluck('id'))
            ->get(['attendance_record_id', 'started_at', 'ended_at'])
            ->groupBy('attendance_record_id');

        foreach ($records as $record) {
            $key = Carbon::parse($record->work_date)->format('Y-m');
            if (! $months->has($key)) {
                continue;
            }

            $pairs = ($breaks[$record->id] ?? collect())->map(fn ($b) => [$b->started_at, $b->ended_at]);
            $months[$key] += self::workedSeconds($record->clock_in_at, $record->clock_out_at, $pairs);
        }

        return $months->map(fn ($seconds, $month) => [
            'month' => $month,
            'workedHours' => (int) $seconds,
        ])->values()->all();
    }

    /** The sprint that contains today, otherwise the latest one that already started. */
    private function currentSprint(): ?object
    {
        $today = $this->today()->toDateString();

        return DB::table('sprints')
            ->where('start_date', '<=', $today)->where('end_date', '>=', $today)
            ->first()
            ?? DB::table('sprints')->where('start_date', '<=', $today)->orderByDesc('start_date')->first();
    }

    /** One row per (done task, assignee) in the sprint. */
    private function doneAssignments(string $sprintId): \Illuminate\Database\Query\Builder
    {
        return DB::table('tasks')
            ->join('task_assignees', 'task_assignees.task_id', '=', 'tasks.id')
            ->where('tasks.sprint_id', $sprintId)
            ->where('tasks.status', 'done');
    }

    /** "Today" in the company timezone (company_settings.timezone, falls back to app timezone). */
    private function today(): Carbon
    {
        $timezone = DB::table('company_settings')->value('timezone') ?: config('app.timezone');

        return Carbon::now($timezone)->startOfDay();
    }
}
