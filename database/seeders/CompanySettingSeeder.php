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
        CompanySetting::firstOrCreate([], [
            'name' => 'Default Company',
            'email' => 'company@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St, Anytown, USA',
            'latitude' => 30.0444000,
            'longitude' => 31.2357000,
            'allowed_radius_meters' => 100.00,
            'timezone' => 'Africa/Cairo',
            'pay_period_start_day' => 1,
            'leave_year_start_month' => 1,
        ]);
    }
}