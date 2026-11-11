<?php

namespace App\Http\Controllers\Web;

use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\Technician;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'customers' => Customer::count(),
            'technicians' => Technician::count(),
            'work_orders' => WorkOrder::count(),
            'active_work_orders' => WorkOrder::whereNotIn('status', [WorkOrderStatus::Completed->value, WorkOrderStatus::Cancelled->value])->count(),
            'parts' => Part::count(),
            'low_stock_parts' => Part::lowStock()->count(),
            'revenue' => Invoice::where('status', '!=', 'cancelled')->sum('paid_amount'),
            'unpaid_invoices' => Invoice::whereIn('status', ['unpaid', 'partial'])->count(),
        ];

        $statusCounts = WorkOrder::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentWorkOrders = WorkOrder::with('customer', 'device', 'technician')
            ->latest()
            ->limit(8)
            ->get();

        $recentCustomers = Customer::withCount('workOrders')
            ->latest()
            ->limit(6)
            ->get();

        $workload = DB::table('technicians')
            ->leftJoin('work_orders', 'work_orders.technician_id', '=', 'technicians.id')
            ->whereIn('work_orders.status', [
                WorkOrderStatus::New->value,
                WorkOrderStatus::Diagnosed->value,
                WorkOrderStatus::Approved->value,
                WorkOrderStatus::Ready->value,
                WorkOrderStatus::InRepair->value,
            ])
            ->selectRaw('technicians.name, COUNT(work_orders.id) as active')
            ->groupBy('technicians.id', 'technicians.name')
            ->orderByDesc('active')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'statusCounts',
            'recentWorkOrders',
            'recentCustomers',
            'workload'
        ));
    }
}
