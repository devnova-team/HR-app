<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** php artisan db:seed --class=ProfileModuleSeeder (after the employees are seeded) */
class ProfileModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssetSeeder::class,
            PayrollRecordSeeder::class,
        ]);
    }
}
