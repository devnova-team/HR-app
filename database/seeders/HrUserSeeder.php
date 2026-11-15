<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Level;
use Illuminate\Database\Seeder;
use RuntimeException;

class HrUserSeeder extends Seeder
{
    /**
     * Create the first HR account so the /admin endpoints can be used.
     *
     * The employees table requires a level, department and job title, so this
     * seeder must run after LevelSeeder and DepartmentSeeder. The location is
     * optional in the database, so it is filled only when a city exists.
     */
    public function run(): void
    {
        $account = config('employees.hr_account');

        if (empty($account['password'])) {
            throw new RuntimeException('Set HR_SEED_PASSWORD in .env before running the seeders.');
        }

        $level = Level::query()->orderByDesc('rank')->first()
            ?? throw new RuntimeException('No levels found. Run LevelSeeder first.');

        $department = Department::query()->orderBy('name')->first()
            ?? throw new RuntimeException('No departments found. Run DepartmentSeeder first.');

        $jobTitle = JobTitle::query()
            ->where('department_id', $department->id)
            ->orderBy('name')
            ->first()
            ?? throw new RuntimeException('The first department has no job titles.');

        $city = City::query()->with('governorate')->first();

        $employeeId = config('employees.employee_id.prefix')
            .str_pad('1', config('employees.employee_id.padding'), '0', STR_PAD_LEFT);

        $hr = Employee::query()->firstOrCreate(
            ['email' => $account['email']],
            [
                'employee_id' => $employeeId,
                'first_name' => $account['first_name'],
                'last_name' => $account['last_name'],
                'phone_number' => $account['phone_number'],
                'full_address' => $account['full_address'],
                'password' => $account['password'],
                'level_id' => $level->id,
                'department_id' => $department->id,
                'job_title_id' => $jobTitle->id,
                'country_id' => $city?->governorate->country_id,
                'governorate_id' => $city?->governorate_id,
                'city_id' => $city?->id,
                'is_verified' => true,
                'is_first_login' => false,
                'is_active' => true,
            ],
        );

        $hr->syncRoles(['hr']);
    }
}
