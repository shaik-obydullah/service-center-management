<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    protected $model = Part::class;

    public function definition(): array
    {
        return [
            'part_category_id' => PartCategory::factory(),
            'supplier_id' => Supplier::factory(),
            'name' => fake()->unique()->words(3, true),
            'code' => fake()->unique()->bothify('PART-####'),
            'brand' => fake()->randomElement(['Apple', 'Samsung', 'Xiaomi', 'Dell', 'HP']),
            'model' => fake()->bothify('Model-###'),
            'cost_price' => fake()->randomFloat(2, 100, 5000),
            'selling_price' => fn (array $attrs) => $attrs['cost_price'] * 1.25,
            'quantity' => fake()->numberBetween(0, 50),
            'minimum_stock' => 5,
            'status' => 'active',
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $attributes['minimum_stock'] - 1,
        ]);
    }
}
