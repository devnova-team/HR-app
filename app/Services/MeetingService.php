<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Meeting;
use App\Repositories\EmployeeRepository;
use App\Repositories\MeetingRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MeetingService
{
    public function __construct(
        protected MeetingRepository $repository,
        protected EmployeeRepository $employees,
        protected NotificationService $notifications
    ) {}

    /** "Today" is the company's day (company_settings.timezone), not the server's. */
    public function today(Employee $employee): Collection
    {
        $companyTimezone = $this->repository->companyTimezone() ?: config('app.timezone');
        $storageTimezone = config('app.timezone');

        return $this->repository->forEmployeeBetween(
            $employee,
            now($companyTimezone)->startOfDay()->setTimezone($storageTimezone),
            now($companyTimezone)->endOfDay()->setTimezone($storageTimezone)
        );
    }

    public function create(array $data): Meeting
    {
        $storageTimezone = config('app.timezone');

        $meeting = DB::transaction(function () use ($data, $storageTimezone) {
            $meeting = $this->repository->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at' => Carbon::parse($data['startTime'])->setTimezone($storageTimezone),
                'ends_at' => Carbon::parse($data['endTime'])->setTimezone($storageTimezone),
                'meeting_url' => $data['meetingLink'],
            ]);

            $this->repository->syncParticipants($meeting, $data['participantIds']);

            foreach ($this->employees->findManyByIds($data['participantIds']) as $participant) {
                $this->notifications->send($participant, 'New meeting', $meeting->title, 'meeting', $meeting->id);
            }

            return $meeting;
        });

        return $this->repository->findWithParticipants($meeting->id);
    }
}
