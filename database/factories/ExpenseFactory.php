<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'reviewed_by_id' => null,
            'payroll_record_id' => null,
            'amount' => fake()->randomFloat(2, 10, 2500),
            'currency' => 'USD',
            'transaction_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'description' => fake()->sentence(),
            'document_url' => fake()->imageUrl(),
            'status' => ExpenseStatus::Review,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ExpenseStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => ExpenseStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => 'Receipt is not clear or invalid.',
        ]);
    }
}
