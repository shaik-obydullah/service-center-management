<?php

namespace Tests\Unit\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Part;
use App\Models\WorkOrder;
use App\Services\PartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PartService
    {
        return app(PartService::class);
    }

    public function test_use_part_deducts_stock_and_records_usage_and_movement(): void
    {
        $part = Part::factory()->create(['quantity' => 10, 'selling_price' => 500]);
        $workOrder = WorkOrder::factory()->create();

        $this->service()->usePart($workOrder, $part->id, 3);

        $this->assertSame(7, $part->fresh()->quantity);
        $this->assertDatabaseHas('part_usage', [
            'work_order_id' => $workOrder->id,
            'part_id' => $part->id,
            'quantity' => 3,
            'unit_price' => 500,
            'total' => 1500,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'type' => 'out',
            'quantity' => 3,
            'reference' => 'WO-' . $workOrder->order_number,
        ]);
    }

    public function test_use_part_throws_when_stock_is_insufficient(): void
    {
        $part = Part::factory()->create(['quantity' => 2]);
        $workOrder = WorkOrder::factory()->create();

        $this->expectException(InsufficientStockException::class);

        $this->service()->usePart($workOrder, $part->id, 5);

        $this->assertSame(2, $part->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_use_parts_handles_multiple_items_transactionally(): void
    {
        $partA = Part::factory()->create(['quantity' => 10, 'selling_price' => 100]);
        $partB = Part::factory()->create(['quantity' => 10, 'selling_price' => 200]);
        $workOrder = WorkOrder::factory()->create();

        $usages = $this->service()->useParts($workOrder, [
            ['part_id' => $partA->id, 'quantity' => 4],
            ['part_id' => $partB->id, 'quantity' => 2],
        ]);

        $this->assertCount(2, $usages);
        $this->assertSame(6, $partA->fresh()->quantity);
        $this->assertSame(8, $partB->fresh()->quantity);
    }

    public function test_restock_increases_quantity_and_logs_inbound_movement(): void
    {
        $part = Part::factory()->create(['quantity' => 5]);

        $this->service()->restock($part, 15, 'PO-2026-00001', 'Received from supplier');

        $this->assertSame(20, $part->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'type' => 'in',
            'quantity' => 15,
            'reference' => 'PO-2026-00001',
        ]);
    }

    public function test_adjust_applies_positive_and_negative_delta(): void
    {
        $part = Part::factory()->create(['quantity' => 10]);

        $this->service()->adjust($part, -3, 'Damaged on arrival');
        $this->assertSame(7, $part->fresh()->quantity);

        $this->service()->adjust($part, 5, 'Inventory count correction');
        $this->assertSame(12, $part->fresh()->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'type' => 'adjustment',
            'quantity' => -3,
        ]);
    }

    public function test_low_stock_parts_only_returns_parts_at_or_below_minimum(): void
    {
        Part::factory()->create(['quantity' => 20, 'minimum_stock' => 5, 'status' => 'active']);
        $low = Part::factory()->create(['quantity' => 4, 'minimum_stock' => 5, 'status' => 'active']);
        Part::factory()->create(['quantity' => 1, 'minimum_stock' => 5, 'status' => 'inactive']);

        $result = $this->service()->lowStockParts();

        $this->assertTrue($result->contains('id', $low->id));
        $this->assertSame(1, $result->count());
    }
}
