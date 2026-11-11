<?php

namespace Database\Factories;

use App\Models\PartCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartCategory>
 */
class PartCategoryFactory extends Factory
{
    protected $model = PartCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Screens', 'Batteries', 'Charging Ports', 'Camera Modules', 'Logic Boards']),
            'status' => true,
        ];
    }
}
