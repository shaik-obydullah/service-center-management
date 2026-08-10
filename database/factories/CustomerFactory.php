<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'nid_number' => fake()->optional()->numerify('##########'),
            'contact_preference' => fake()->randomElement(['phone', 'email', 'sms']),
            'loyalty_member' => fake()->boolean(20),
        ];
    }
}
