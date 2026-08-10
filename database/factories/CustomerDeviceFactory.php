<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerDevice>
 */
class CustomerDeviceFactory extends Factory
{
    protected $model = CustomerDevice::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'device_type_id' => null,
            'type' => fake()->randomElement(['Phone', 'Laptop', 'Tablet']),
            'brand' => fake()->randomElement(['Apple', 'Samsung', 'Xiaomi', 'Dell', 'HP']),
            'model' => fake()->bothify('Model-###'),
            'serial_number' => fake()->unique()->bothify('SN-####-####'),
            'color' => fake()->safeColorName(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
