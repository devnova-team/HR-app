<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Seed the levels listed in config/employees.php (rank => name).
     */
    public function run(): void
    {
        foreach (config('employees.levels') as $rank => $name) {
            Level::query()->updateOrCreate(
                ['rank' => $rank],
                ['name' => $name],
            );
        }
    }
}
