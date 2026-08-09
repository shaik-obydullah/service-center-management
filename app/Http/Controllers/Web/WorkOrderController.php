<?php

namespace App\Http\Controllers\Web;

use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeWorkOrderStatusRequest;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Http\Requests\UsePartsRequest;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\DeviceType;
use App\Models\Part;
use App\Models\RepairService;
use App\Models\Technician;
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
            ->when($request->search, fn ($q, $s) => $q
                ->where('order_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($c) => $c
                    ->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = collect(WorkOrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]);

        return view('work-orders.index', compact('workOrders', 'statuses'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $technicians = Technician::where('status', 'active')->get();
        $repairServices = RepairService::where('status', true)->get();
        $deviceTypes = DeviceType::where('status', true)->get();

        $selectedCustomer = $request->customer_id ? Customer::find($request->customer_id) : null;
        $devices = $selectedCustomer
            ? CustomerDevice::where('customer_id', $selectedCustomer->id)->get()
            : collect();

        return view('work-orders.create', compact(
            'customers',
            'technicians',
            'repairServices',
            'deviceTypes',
            'selectedCustomer',
            'devices'
        ));
    }

    public function store(StoreWorkOrderRequest $request, WorkOrderService $service)
    {
        $workOrder = $service->create($request->validated(), auth()->id());

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', "Work order {$workOrder->order_number} created successfully.");
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'customer',
            'device',
            'technician',
            'repairService',
            'creator',
            'statusHistory.user',
            'notes.user',
            'partUsages.part',
            'invoice.payments',
            'warranties',
        ]);

        $workOrder->loadCount('partUsages');

        return view('work-orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $customers = Customer::orderBy('name')->get();
        $technicians = Technician::where('status', 'active')->get();
        $repairServices = RepairService::where('status', true)->get();
        $devices = CustomerDevice::where('customer_id', $workOrder->customer_id)->get();

        return view('work-orders.edit', compact('workOrder', 'customers', 'technicians', 'repairServices', 'devices'));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        $workOrder->update($request->validated());

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', 'Work order updated successfully.');
    }

    public function assign(Request $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        $request->validate(['technician_id' => ['required', 'exists:technicians,id']]);

        $service->assignTechnician($workOrder, $request->technician_id, auth()->id());

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', 'Technician assigned successfully.');
    }

    public function changeStatus(ChangeWorkOrderStatusRequest $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        try {
            $service->changeStatus($workOrder, $request->status, auth()->id(), $request->notes);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('work-orders.show', $workOrder)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', "Status changed to {$request->status}.");
    }

    public function addNote(Request $request, WorkOrder $workOrder, WorkOrderService $service)
    {
        $request->validate(['note' => ['required', 'string', 'max:2000']]);

        $service->addNote($workOrder, $request->note, auth()->id());

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', 'Note added.');
    }

    public function useParts(UsePartsRequest $request, WorkOrder $workOrder, PartService $parts)
    {
        try {
            $parts->useParts($workOrder, $request->items, auth()->id());
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return redirect()
                ->route('work-orders.show', $workOrder)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', 'Parts added to the work order.');
    }

    public function devicesJson(Request $request)
    {
        $request->validate(['customer_id' => ['required', 'exists:customers,id']]);

        return CustomerDevice::where('customer_id', $request->customer_id)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'label' => "{$d->brand} {$d->model} ({$d->serial_number})",
            ]);
    }
}
