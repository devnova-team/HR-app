<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // make empty array for firstOrCreate to avoid duplicate entries
        CompanySetting::firstOrCreate([], [
            'name' => 'Default Company',
            'email' => 'company@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St, Anytown, USA',
            'latitude' => 0.0000000,
            'longitude' => 0.0000000,
            'allowed_radius_meters' => 100.00,
            'timezone' => 'UTC',
            'pay_period_start_day' => 1,
            'leave_year_start_month' => 1,
        ]);
    }
}
