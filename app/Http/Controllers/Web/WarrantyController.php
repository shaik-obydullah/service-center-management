<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarrantyRequest;
use App\Models\Warranty;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $warranties = Warranty::query()
            ->with('workOrder.customer', 'workOrder.device', 'part')
            ->when($request->status === 'active', fn ($q) => $q
                ->where('status', 'active')
                ->whereDate('end_date', '>=', now()->toDateString()))
            ->when($request->status === 'expired', fn ($q) => $q
                ->where('status', 'active')
                ->whereDate('end_date', '<', now()->toDateString()))
            ->when($request->status === 'revoked', fn ($q) => $q->where('status', 'revoked'))
            ->when($request->search, fn ($q, $s) => $q
                ->where(fn ($q) => $q
                    ->whereHas('workOrder', fn ($wo) => $wo
                        ->where('order_number', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%")))
                    ->orWhereHas('part', fn ($p) => $p->where('name', 'like', "%{$s}%"))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('warranties.index', compact('warranties'));
    }

    public function store(StoreWarrantyRequest $request, WorkOrder $workOrder)
    {
        $warranty = $workOrder->warranties()->create([
            'part_id' => $request->part_id,
            'duration_days' => $request->duration_days,
            'start_date' => $request->start_date,
            'end_date' => Carbon::parse($request->start_date)->addDays((int) $request->duration_days),
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('work-orders.show', $workOrder)
            ->with('success', "Warranty of {$warranty->duration_days} days added to {$workOrder->order_number}.");
    }

    public function show(Warranty $warranty)
    {
        $warranty->load('workOrder.customer', 'workOrder.device', 'workOrder.partUsages.part', 'part');

        return view('warranties.show', compact('warranty'));
    }

    public function revoke(Warranty $warranty)
    {
        if ($warranty->status === 'revoked') {
            return redirect()
                ->route('warranties.show', $warranty)
                ->with('info', 'This warranty is already revoked.');
        }

        $warranty->update(['status' => 'revoked']);

        return redirect()
            ->route('warranties.show', $warranty)
            ->with('success', 'Warranty revoked.');
    }
}
