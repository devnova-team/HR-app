<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobTitle;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Department name => job titles.
     *
     * Only titles that appear in the project documents are listed. Add the
     * real departments and job titles agreed with the team lead.
     *
     * @var array<string, array<int, string>>
     */
    private const DEPARTMENTS = [
        'IT' => [
            'Flutter Developer',
            'Front-End Developer',
            'Back-End Developer',
            'Full Stack Developer',
        ],
    ];

    public function run(): void
    {
        foreach (self::DEPARTMENTS as $departmentName => $jobTitles) {
            $department = Department::query()->firstOrCreate(['name' => $departmentName]);

            foreach ($jobTitles as $jobTitleName) {
                JobTitle::query()->firstOrCreate([
                    'department_id' => $department->id,
                    'name' => $jobTitleName,
                ]);
            }
        }
    }
}
