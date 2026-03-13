<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'department' => fake()->randomElement([
                'Engineering',
                'Marketing',
                'Sales',
                'HR',
                'Finance',
                'Operations'
            ]),
            'position' => fake()->randomElement([
                'Manager',
                'Senior Developer',
                'Developer',
                'Designer',
                'Analyst',
                'Coordinator'
            ]),
            'salary' => fake()->randomFloat(2, 30000, 150000),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}