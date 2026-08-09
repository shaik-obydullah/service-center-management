<?php

namespace App\Http\Controllers\Web;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier', 'items.part')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = collect(PurchaseOrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]);

        return view('parts.purchase-orders.index', compact('purchaseOrders', 'statuses'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', true)->pluck('name', 'id');
        $parts = Part::where('status', 'active')->orderBy('name')->get();

        return view('parts.purchase-orders.create', compact('suppliers', 'parts'));
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = DB::transaction(function () use ($request) {
            $total = collect($request->items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->nextPoNumber(),
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'status' => PurchaseOrderStatus::Pending->value,
                'total_amount' => $total,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $purchaseOrder->items()->create([
                    'part_id' => $item['part_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $purchaseOrder;
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.part', 'creator');

        return view('parts.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Mark a purchase order as received and restock inventory.
     */
    public function receive(PurchaseOrder $purchaseOrder, PartService $service)
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::Pending->value) {
            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only pending purchase orders can be received.');
        }

        DB::transaction(function () use ($purchaseOrder, $service) {
            foreach ($purchaseOrder->items as $item) {
                $service->restock(
                    $item->part,
                    $item->quantity,
                    $purchaseOrder->po_number,
                    "Received from purchase order {$purchaseOrder->po_number}",
                    auth()->id()
                );
            }

            $purchaseOrder->status = PurchaseOrderStatus::Received->value;
            $purchaseOrder->save();
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order received and stock updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === PurchaseOrderStatus::Received->value) {
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', 'Cannot delete a received purchase order.');
        }

        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted.');
    }

    protected function nextPoNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'PO-' . $year . '-';

        $last = PurchaseOrder::query()
            ->where('po_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('po_number');

        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
