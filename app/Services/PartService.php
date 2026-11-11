<?php

namespace App\Services;

use App\Enums\WorkOrderStatus;
use App\Events\LowStockAlert;
use App\Models\Part;
use App\Models\PartUsage;
use App\Models\StockMovement;
use App\Models\WorkOrder;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class PartService
{
    /**
     * Use parts against a work order. Deducts stock, records movement
     * and links usage to the work order.
     *
     * @param  array<int, array{part_id: int, quantity: int}>  $items
     */
    public function useParts(WorkOrder $workOrder, array $items, ?int $userId = null): array
    {
        return DB::transaction(function () use ($workOrder, $items, $userId) {
            $usages = [];

            foreach ($items as $item) {
                $usages[] = $this->usePart(
                    $workOrder,
                    $item['part_id'],
                    $item['quantity'],
                    $userId
                );
            }

            return $usages;
        });
    }

    public function usePart(WorkOrder $workOrder, int $partId, int $quantity, ?int $userId = null): PartUsage
    {
        $part = Part::lockForUpdate()->findOrFail($partId);

        if ($part->quantity < $quantity) {
            throw new InsufficientStockException($part->name);
        }

        $part->quantity -= $quantity;
        $part->save();

        StockMovement::create([
            'part_id' => $partId,
            'type' => 'out',
            'quantity' => $quantity,
            'reference' => "WO-{$workOrder->order_number}",
            'notes' => "Part used for work order {$workOrder->order_number}",
            'user_id' => $userId,
        ]);

        $usage = PartUsage::create([
            'work_order_id' => $workOrder->id,
            'part_id' => $partId,
            'quantity' => $quantity,
            'unit_price' => $part->selling_price,
            'total' => $quantity * $part->selling_price,
        ]);

        if ($part->quantity <= $part->minimum_stock) {
            event(new LowStockAlert($part));
        }

        return $usage;
    }

    /**
     * Receive stock into inventory (purchase order / manual adjustment).
     */
    public function restock(Part $part, int $quantity, string $reference = null, string $notes = null, ?int $userId = null): Part
    {
        return DB::transaction(function () use ($part, $quantity, $reference, $notes, $userId) {
            $part->quantity += $quantity;
            $part->save();

            StockMovement::create([
                'part_id' => $part->id,
                'type' => 'in',
                'quantity' => $quantity,
                'reference' => $reference,
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            return $part->fresh();
        });
    }

    /**
     * Adjust stock with a positive or negative delta.
     */
    public function adjust(Part $part, int $delta, string $reason = null, ?int $userId = null): Part
    {
        return DB::transaction(function () use ($part, $delta, $reason, $userId) {
            $part->quantity += $delta;
            $part->save();

            StockMovement::create([
                'part_id' => $part->id,
                'type' => 'adjustment',
                'quantity' => $delta,
                'reference' => 'manual-adjustment',
                'notes' => $reason,
                'user_id' => $userId,
            ]);

            return $part->fresh();
        });
    }

    public function lowStockParts(): \Illuminate\Database\Eloquent\Collection
    {
        return Part::lowStock()->get();
    }
}
