<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_id' => 'EMP-' . Str::uuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->numerify('01#########'),
            'password' => Hash::make('password'),
            'full_address' => fake()->address(),
            'is_verified' => true,
            'is_first_login' => false,
            'is_active' => true,
        ];
    }
}