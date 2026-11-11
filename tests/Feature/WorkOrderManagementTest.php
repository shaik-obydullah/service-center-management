<?php

namespace Tests\Feature;

use App\Enums\WorkOrderStatus;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('work-orders.create'))
            ->assertOk()
            ->assertSee('New Work Order');
    }

    public function test_user_can_create_a_work_order(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $device = CustomerDevice::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($user)
            ->post(route('work-orders.store'), [
                'customer_id' => $customer->id,
                'device_id' => $device->id,
                'problem_description' => 'Screen cracked',
                'priority' => 'high',
                'estimated_cost' => 2500,
                'estimated_date' => now()->addWeek()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('work_orders', 1);

        $workOrder = WorkOrder::first();
        $this->assertSame('new', $workOrder->status);
        $this->assertStringStartsWith('WO-' . now()->year . '-', $workOrder->order_number);
        $this->assertSame($user->id, $workOrder->created_by);
        $this->assertDatabaseHas('work_order_status_history', [
            'work_order_id' => $workOrder->id,
            'status' => 'new',
        ]);
    }

    public function test_work_order_requires_customer_and_device(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('work-orders.store'), ['problem_description' => 'Broken'])
            ->assertSessionHasErrors(['customer_id', 'device_id', 'priority', 'estimated_cost']);

        $this->assertDatabaseCount('work_orders', 0);
    }

    public function test_show_page_displays_work_order_details(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
            ->get(route('work-orders.show', $workOrder))
            ->assertOk()
            ->assertSee($workOrder->order_number)
            ->assertSee($workOrder->problem_description);
    }

    public function test_user_can_assign_a_technician(): void
    {
        $user = User::factory()->create();
        $technician = Technician::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('work-orders.assign', $workOrder), ['technician_id' => $technician->id])
            ->assertRedirect(route('work-orders.show', $workOrder));

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'technician_id' => $technician->id,
        ]);
        $this->assertDatabaseHas('technician_assignments', [
            'work_order_id' => $workOrder->id,
            'technician_id' => $technician->id,
        ]);
    }

    public function test_status_can_be_changed_along_expected_workflow(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['status' => 'new', 'created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('work-orders.status', $workOrder), [
                'status' => WorkOrderStatus::Diagnosed->value,
                'notes' => 'Diagnosed as cracked screen',
            ])
            ->assertRedirect(route('work-orders.show', $workOrder));

        $this->assertSame('diagnosed', $workOrder->fresh()->status);
        $this->assertDatabaseHas('work_order_status_history', [
            'work_order_id' => $workOrder->id,
            'status' => 'diagnosed',
            'notes' => 'Diagnosed as cracked screen',
        ]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['status' => 'new', 'created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('work-orders.status', $workOrder), ['status' => 'completed'])
            ->assertRedirect(route('work-orders.show', $workOrder))
            ->assertSessionHas('error');

        $this->assertSame('new', $workOrder->fresh()->status);
    }

    public function test_completing_work_order_sets_completed_at(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['status' => 'in_repair', 'created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('work-orders.status', $workOrder), ['status' => 'completed'])
            ->assertRedirect(route('work-orders.show', $workOrder));

        $this->assertSame('completed', $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->completed_at);
    }

    public function test_index_lists_work_orders(): void
    {
        $user = User::factory()->create();
        WorkOrder::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('work-orders.index'))
            ->assertOk()
            ->assertSee('Work Orders');
    }
}
