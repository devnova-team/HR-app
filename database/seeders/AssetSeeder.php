<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\EmployeeAsset;
use Illuminate\Database\Seeder;

/** Demo data: a small pool of assets, 1-3 of them assigned to each active employee. */
class AssetSeeder extends Seeder
{
    public function run(): void
    {
        if (Asset::query()->exists()) {
            return; // already seeded
        }

        $pool = Asset::factory()->count(10)->create();

        Employee::query()->where('is_active', true)->each(function (Employee $employee) use ($pool) {
            $pool->random(random_int(1, 3))->each(fn (Asset $asset) => EmployeeAsset::create([
                'employee_id' => $employee->id,
                'asset_id' => $asset->id,
                'assigned_at' => now()->subDays(random_int(30, 400)),
            ]));
        });
    }
}
