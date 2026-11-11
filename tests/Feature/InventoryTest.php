<?php

namespace Tests\Feature;

use App\Models\CustomerDevice;
use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_parts_index_renders(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('parts.index'))
            ->assertOk()
            ->assertSee('Parts');
    }

    public function test_user_can_create_a_part(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('parts.store'), [
                'name' => 'iPhone 14 Screen',
                'code' => 'SCR-IP14',
                'supplier_id' => $supplier->id,
                'brand' => 'Apple',
                'model' => 'iPhone 14',
                'cost_price' => 5000,
                'selling_price' => 6500,
                'quantity' => 10,
                'minimum_stock' => 3,
            ])
            ->assertRedirect(route('parts.index'));

        $this->assertDatabaseHas('parts', ['code' => 'SCR-IP14', 'quantity' => 10]);
    }

    public function test_low_stock_query_only_returns_parts_at_or_below_minimum(): void
    {
        Part::factory()->create(['quantity' => 10, 'minimum_stock' => 5, 'status' => 'active']);
        $low = Part::factory()->create(['quantity' => 4, 'minimum_stock' => 5, 'status' => 'active']);
        Part::factory()->create(['quantity' => 2, 'minimum_stock' => 5, 'status' => 'inactive']);

        $this->assertTrue(Part::lowStock()->get()->contains('id', $low->id));
        $this->assertSame(1, Part::lowStock()->count());
    }

    public function test_receiving_purchase_order_restocks_inventory(): void
    {
        $supplier = Supplier::factory()->create();
        $part = Part::factory()->create(['quantity' => 5]);

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-2026-00001',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'status' => 'pending',
            'total_amount' => 1000,
            'created_by' => null,
        ]);
        $purchaseOrder->items()->create([
            'part_id' => $part->id,
            'quantity' => 15,
            'unit_price' => 100,
            'total' => 1500,
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('purchase-orders.receive', $purchaseOrder))
            ->assertRedirect(route('purchase-orders.show', $purchaseOrder));

        $this->assertSame(20, $part->fresh()->quantity);
        $this->assertSame('received', $purchaseOrder->fresh()->status);
        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'type' => 'in',
            'quantity' => 15,
        ]);
    }

    public function test_used_parts_are_linked_to_work_order_and_stock_deducted(): void
    {
        $user = User::factory()->create();
        $part = Part::factory()->create(['quantity' => 10, 'selling_price' => 500]);
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('work-orders.parts', $workOrder), [
                'items' => [
                    ['part_id' => $part->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect(route('work-orders.show', $workOrder));

        $this->assertSame(7, $part->fresh()->quantity);
        $this->assertDatabaseHas('part_usage', [
            'work_order_id' => $workOrder->id,
            'part_id' => $part->id,
            'quantity' => 3,
            'total' => 1500,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'type' => 'out',
            'quantity' => 3,
        ]);
    }

    public function test_using_more_than_available_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $part = Part::factory()->create(['quantity' => 2]);
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('work-orders.parts', $workOrder), [
                'items' => [
                    ['part_id' => $part->id, 'quantity' => 5],
                ],
            ])
            ->assertRedirect(route('work-orders.show', $workOrder))
            ->assertSessionHas('error');

        $this->assertSame(2, $part->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('part_usage', 0);
    }

    public function test_warranty_can_be_added_to_work_order(): void
    {
        $user = User::factory()->create();
        $part = Part::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
            ->post(route('warranties.store', $workOrder), [
                'part_id' => $part->id,
                'duration_days' => 90,
                'start_date' => now()->toDateString(),
                'notes' => 'Screen warranty',
            ])
            ->assertRedirect(route('work-orders.show', $workOrder));

        $this->assertDatabaseHas('warranties', [
            'work_order_id' => $workOrder->id,
            'part_id' => $part->id,
            'duration_days' => 90,
            'status' => 'active',
        ]);

        $warranty = Warranty::first();
        $this->assertEquals(now()->addDays(90)->toDateString(), $warranty->end_date->toDateString());
    }

    public function test_warranty_can_be_revoked(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);
        $warranty = $workOrder->warranties()->create([
            'duration_days' => 30,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('warranties.revoke', $warranty))
            ->assertRedirect(route('warranties.show', $warranty));

        $this->assertSame('revoked', $warranty->fresh()->status);
        $this->assertSame('Revoked', $warranty->fresh()->status_label);
    }

    public function test_expired_warranty_is_marked_as_expired(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $warranty = $workOrder->warranties()->create([
            'duration_days' => 30,
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(30),
            'status' => 'active',
        ]);

        $this->assertTrue($warranty->is_expired);
        $this->assertSame('Expired', $warranty->status_label);
        $this->assertSame('red', $warranty->status_badge);
    }
}
