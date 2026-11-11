<?php

namespace Database\Factories;

use App\Models\Technician;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Technician>
 */
class TechnicianFactory extends Factory
{
    protected $model = Technician::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'employee_id' => fake()->unique()->bothify('TEC-###'),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'skills_json' => fake()->randomElements(['Screen Replacement', 'Battery', 'Diagnostics', 'Board Repair', 'Microsoldering'], 2),
            'hourly_rate' => fake()->randomFloat(2, 200, 800),
            'status' => 'active',
        ];
    }
}
