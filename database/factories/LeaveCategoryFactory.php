<?php

namespace Database\Factories;

use App\Models\LeaveCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveCategory>
 */
class LeaveCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'default_days' => 21,
            'is_paid' => true,
            'requires_balance' => true,
            'is_active' => true,
        ];
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'default_days' => 0,
            'is_paid' => false,
            'requires_balance' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
