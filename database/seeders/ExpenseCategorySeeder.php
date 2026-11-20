<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Travel', 'is_active' => true],
            ['name' => 'Transportation', 'is_active' => true],
            ['name' => 'Meals & Entertainment', 'is_active' => true],
            ['name' => 'Office Supplies', 'is_active' => true],
            ['name' => 'Hardware & Equipment', 'is_active' => true],
            ['name' => 'Other', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
