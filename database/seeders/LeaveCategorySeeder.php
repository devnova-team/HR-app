<?php

namespace Database\Seeders;

use App\Models\LeaveCategory;
use Illuminate\Database\Seeder;

class LeaveCategorySeeder extends Seeder
{
    /**
     * Default leave categories matching the API documentation examples.
     *
     * @var array<int, array{name: string, default_days: float, is_paid: bool, requires_balance: bool}>
     */
    private const CATEGORIES = [
        [
            'name' => 'Annual',
            'default_days' => 21,
            'is_paid' => true,
            'requires_balance' => true,
        ],
        [
            'name' => 'Sick',
            'default_days' => 15,
            'is_paid' => true,
            'requires_balance' => true,
        ],
        [
            'name' => 'Emergency',
            'default_days' => 6,
            'is_paid' => true,
            'requires_balance' => true,
        ],
        [
            'name' => 'Unpaid',
            'default_days' => 0,
            'is_paid' => false,
            'requires_balance' => false,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            LeaveCategory::query()->firstOrCreate(
                ['name' => $category['name']],
                [
                    'default_days' => $category['default_days'],
                    'is_paid' => $category['is_paid'],
                    'requires_balance' => $category['requires_balance'],
                    'is_active' => true,
                ],
            );
        }
    }
}
