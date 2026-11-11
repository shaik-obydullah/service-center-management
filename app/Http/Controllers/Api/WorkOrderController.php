<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeWorkOrderStatusRequest;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Http\Requests\UsePartsRequest;
use App\Models\WorkOrder;
use App\Services\PartService;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $workOrders = WorkOrder::query()
            ->with('customer', 'device', 'technician', 'invoice')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($workOrders);
    }

    public function store(StoreWorkOrderRequest $request, WorkOrderService $service)
    {
        $workOrder = $service->create($request->validated(), $request->user()?->id);

        return response()->json(['message' => 'Work order created.', 'work_order' => $workOrder], 201);
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load('customer', 'device', 'technician', 'repairService', 'statusHistory.user', 'notes.user', 'partUsages.part', 'invoice.payments');

        return response()->json($workOrder);
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder)
    {
        $workOrder->update($request->validated());

        return response()->json(['message' => 'Work order updated.', 'work_order' => $workOrder->fresh()]);
    }

    public function assign(Request $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        $request->validate(['technician_id' => ['required', 'exists:technicians,id']]);

        $workOrder = $service->assignTechnician($workOrder, $request->technician_id, $request->user()?->id);

        return response()->json(['message' => 'Technician assigned.', 'work_order' => $workOrder]);
    }

    public function status(ChangeWorkOrderStatusRequest $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        try {
            $workOrder = $service->changeStatus($workOrder, $request->status, $request->user()?->id, $request->notes);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Status updated.', 'work_order' => $workOrder]);
    }

    public function history(WorkOrder $workOrder)
    {
        return response()->json($workOrder->statusHistory()->with('user')->latest()->get());
    }

    public function notes(WorkOrder $workOrder)
    {
        return response()->json($workOrder->notes()->with('user')->latest()->get());
    }

    public function addNote(Request $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        $request->validate(['note' => ['required', 'string', 'max:2000']]);

        $note = $service->addNote($workOrder, $request->note, $request->user()?->id);

        return response()->json(['message' => 'Note added.', 'note' => $note], 201);
    }

    public function useParts(UsePartsRequest $request, WorkOrder $workOrder, PartService $parts)
    {
        try {
            $usages = $parts->useParts($workOrder, $request->items, $request->user()?->id);
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Parts used.', 'usages' => $usages], 201);
    }
}
