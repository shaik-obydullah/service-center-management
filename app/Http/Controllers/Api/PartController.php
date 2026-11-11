<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartRequest;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Http\Requests\UsePartsRequest;
use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Services\PartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $parts = Part::query()
            ->with('partCategory', 'supplier')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%"))
            ->when($request->low === '1', fn ($q) => $q->lowStock())
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($parts);
    }

    public function store(StorePartRequest $request)
    {
        $part = Part::create($request->validated());

        return response()->json(['message' => 'Part created.', 'part' => $part], 201);
    }

    public function show(Part $part)
    {
        $part->load('partCategory', 'supplier', 'movements', 'usage');

        return response()->json($part);
    }

    public function update(UpdatePartRequest $request, Part $part)
    {
        $part->update($request->validated());

        return response()->json(['message' => 'Part updated.', 'part' => $part->fresh()]);
    }

    public function lowStock()
    {
        return response()->json(Part::lowStock()->with('supplier')->get());
    }

    public function usage(UsePartsRequest $request, PartService $parts)
    {
        $workOrder = WorkOrder::findOrFail($request->work_order_id);

        try {
            $usages = $parts->useParts($workOrder, $request->items, $request->user()?->id);
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Parts used.', 'usages' => $usages], 201);
    }

    public function restock(Request $request, Part $part, PartService $service)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $part = $service->restock($part, $request->quantity, 'api-restock', $request->notes, $request->user()?->id);

        return response()->json(['message' => 'Stock updated.', 'part' => $part]);
    }

    public function suppliers()
    {
        return response()->json(Supplier::withCount('parts')->get());
    }

    public function storeSupplier(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return response()->json(['message' => 'Supplier created.', 'supplier' => $supplier], 201);
    }

    public function purchaseOrders()
    {
        return response()->json(PurchaseOrder::with('supplier', 'items.part')->latest()->get());
    }

    public function storePurchaseOrder(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = DB::transaction(function () use ($request) {
            $total = collect($request->items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Y') . '-' . str_pad((string) (PurchaseOrder::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'status' => 'pending',
                'total_amount' => $total,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($request->items as $item) {
                $po->items()->create([
                    'part_id' => $item['part_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $po->load('supplier', 'items.part');
        });

        return response()->json(['message' => 'Purchase order created.', 'purchase_order' => $purchaseOrder], 201);
    }
}
