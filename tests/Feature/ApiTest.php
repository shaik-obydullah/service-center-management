<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_api_login_with_invalid_credentials_is_rejected(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_api_routes_require_authentication(): void
    {
        $this->getJson('/api/customers')->assertUnauthorized();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_customers(): void
    {
        $user = User::factory()->create();
        Customer::factory()->count(3)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/customers')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_create_customer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/customers', [
                'name' => 'Rahim Uddin',
                'phone' => '+8801700000004',
                'city' => 'Dhaka',
            ])
            ->assertCreated()
            ->assertJsonPath('customer.name', 'Rahim Uddin');

        $this->assertDatabaseHas('customers', ['name' => 'Rahim Uddin']);
    }

    public function test_authenticated_user_can_fetch_work_orders(): void
    {
        $user = User::factory()->create();
        \App\Models\WorkOrder::factory()->count(2)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/work-orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
