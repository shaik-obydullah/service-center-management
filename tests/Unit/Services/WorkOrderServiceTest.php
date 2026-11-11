<?php

namespace Tests\Unit\Services;

use App\Enums\WorkOrderStatus;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WorkOrderService
    {
        return app(WorkOrderService::class);
    }

    private function makeCustomerWithDevice(): array
    {
        $customer = Customer::factory()->create();
        $device = CustomerDevice::factory()->create(['customer_id' => $customer->id]);

        return [$customer, $device];
    }

    private function payload(array $overrides = []): array
    {
        [$customer, $device] = $this->makeCustomerWithDevice();

        return array_merge([
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'problem_description' => 'Screen cracked',
            'priority' => 'high',
            'estimated_cost' => 3000,
        ], $overrides);
    }

    public function test_create_generates_order_number_and_status_history(): void
    {
        $user = User::factory()->create();

        $workOrder = $this->service()->create($this->payload(), $user->id);

        $this->assertSame('new', $workOrder->status);
        $this->assertStringStartsWith('WO-' . now()->year . '-', $workOrder->order_number);
        $this->assertSame($user->id, $workOrder->created_by);
        $this->assertDatabaseHas('work_order_status_history', [
            'work_order_id' => $workOrder->id,
            'status' => 'new',
            'changed_by' => $user->id,
        ]);
    }

    public function test_next_order_number_increments_sequence(): void
    {
        $first = $this->service()->create($this->payload());
        $second = $this->service()->create($this->payload());

        $this->assertSame('WO-' . now()->year . '-00001', $first->order_number);
        $this->assertSame('WO-' . now()->year . '-00002', $second->order_number);
    }

    public function test_assign_technician_updates_work_order_and_creates_assignment(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $technician = Technician::factory()->create();
        $user = User::factory()->create();

        $this->service()->assignTechnician($workOrder, $technician->id, $user->id);

        $this->assertSame($technician->id, $workOrder->fresh()->technician_id);
        $this->assertDatabaseHas('technician_assignments', [
            'work_order_id' => $workOrder->id,
            'technician_id' => $technician->id,
        ]);
        $this->assertDatabaseHas('work_order_status_history', [
            'work_order_id' => $workOrder->id,
            'changed_by' => $user->id,
        ]);
    }

    public function test_change_status_follows_expected_workflow(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'new']);

        $this->service()->changeStatus($workOrder, WorkOrderStatus::Diagnosed->value);

        $this->assertSame('diagnosed', $workOrder->fresh()->status);
        $this->assertDatabaseHas('work_order_status_history', [
            'work_order_id' => $workOrder->id,
            'status' => 'diagnosed',
        ]);
    }

    public function test_invalid_status_transition_throws(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'new']);

        $this->expectException(\InvalidArgumentException::class);

        $this->service()->changeStatus($workOrder, WorkOrderStatus::Completed->value);

        $this->assertSame('new', $workOrder->fresh()->status);
    }

    public function test_completing_sets_completed_at(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'in_repair']);

        $this->service()->changeStatus($workOrder, WorkOrderStatus::Completed->value);

        $this->assertSame('completed', $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->completed_at);
    }
}
