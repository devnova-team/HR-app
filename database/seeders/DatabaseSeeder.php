<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySettingSeeder::class,
            WorkScheduleSeeder::class,
            EmployeeWorkScheduleSeeder::class,
            AttendanceRecordSeeder::class,
            LevelSeeder::class,
            DepartmentSeeder::class,
            LocationSeeder::class,
            RolesAndPermissionsSeeder::class,
            HrUserSeeder::class,
        ]);
    }
}
