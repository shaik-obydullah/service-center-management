<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('WO-2026-#####'),
            'customer_id' => Customer::factory(),
            'device_id' => CustomerDevice::factory(),
            'technician_id' => null,
            'repair_service_id' => null,
            'problem_description' => fake()->sentence(),
            'diagnosis' => fake()->optional()->sentence(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => 'new',
            'estimated_cost' => fake()->randomFloat(2, 100, 10000),
            'actual_cost' => null,
            'discount' => fake()->randomElement([0, 5, 10]),
            'estimated_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'completed_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
