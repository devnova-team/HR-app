<?php

namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        WorkSchedule::updateOrCreate(
            ['name' => 'Standard'],
            [
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '17:00:00',
                'grace_period_minutes' => 15,
                'is_default' => true,
            ]
        );

        WorkSchedule::updateOrCreate(
            ['name' => 'Evening'],
            [
                'clock_in_time' => '14:00:00',
                'clock_out_time' => '22:00:00',
                'grace_period_minutes' => 10,
                'is_default' => false,
            ]
        );
    }
}