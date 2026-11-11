<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'invoice_number' => fake()->unique()->bothify('INV-2026-#####'),
            'service_charge' => fake()->randomFloat(2, 100, 5000),
            'parts_cost' => fake()->randomFloat(2, 0, 5000),
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ];
    }
}
