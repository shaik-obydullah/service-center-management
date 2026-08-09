<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function revenue(?string $from = null, ?string $to = null): array
    {
        $from = CarbonImmutable::parse($from ?? now()->startOfMonth())->startOfDay();
        $to = CarbonImmutable::parse($to ?? now())->endOfDay();

        $summary = DB::table('invoices')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('COALESCE(SUM(total), 0) as total_billed')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as total_collected')
            ->selectRaw('COALESCE(SUM(tax), 0) as total_tax')
            ->first();

        $daily = DB::table('invoices')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'invoice_count' => (int) $summary->invoice_count,
            'total_billed' => (float) $summary->total_billed,
            'total_collected' => (float) $summary->total_collected,
            'total_tax' => (float) $summary->total_tax,
            'daily' => $daily,
        ];
    }

    public function technicianPerformance(?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        $from = CarbonImmutable::parse($from ?? now()->startOfMonth())->startOfDay();
        $to = CarbonImmutable::parse($to ?? now())->endOfDay();

        return DB::table('technicians')
            ->leftJoin('work_orders', 'work_orders.technician_id', '=', 'technicians.id')
            ->whereBetween('work_orders.created_at', [$from, $to])
            ->selectRaw('technicians.id, technicians.name, technicians.employee_id')
            ->selectRaw('COUNT(work_orders.id) as total_orders')
            ->selectRaw('SUM(CASE WHEN work_orders.status = "completed" THEN 1 ELSE 0 END) as completed_orders')
            ->selectRaw('COALESCE(SUM(work_orders.actual_cost), 0) as revenue')
            ->selectRaw('COALESCE(SUM(technician_assignments.hours_spent), 0) as total_hours')
            ->leftJoin('technician_assignments', function ($join) {
                $join->on('technician_assignments.technician_id', '=', 'technicians.id')
                    ->whereColumn('technician_assignments.work_order_id', '=', 'work_orders.id');
            })
            ->groupBy('technicians.id', 'technicians.name', 'technicians.employee_id')
            ->orderByDesc('completed_orders')
            ->get();
    }

    public function popularRepairs(?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        $from = CarbonImmutable::parse($from ?? now()->startOfMonth())->startOfDay();
        $to = CarbonImmutable::parse($to ?? now())->endOfDay();

        return DB::table('work_orders')
            ->join('repair_services', 'repair_services.id', '=', 'work_orders.repair_service_id')
            ->whereBetween('work_orders.created_at', [$from, $to])
            ->selectRaw('repair_services.id, repair_services.name')
            ->selectRaw('COUNT(work_orders.id) as total_count')
            ->groupBy('repair_services.id', 'repair_services.name')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();
    }

    public function inventory(): array
    {
        $summary = DB::table('parts')
            ->selectRaw('COUNT(*) as part_count')
            ->selectRaw('COALESCE(SUM(quantity * cost_price), 0) as stock_value')
            ->selectRaw('SUM(CASE WHEN quantity <= minimum_stock THEN 1 ELSE 0 END) as low_stock_count')
            ->selectRaw('SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count')
            ->first();

        $byCategory = DB::table('parts')
            ->join('part_categories', 'part_categories.id', '=', 'parts.part_category_id')
            ->selectRaw('part_categories.name, COUNT(parts.id) as part_count, COALESCE(SUM(parts.quantity), 0) as total_quantity')
            ->groupBy('part_categories.name')
            ->orderByDesc('part_count')
            ->get();

        return [
            'part_count' => (int) $summary->part_count,
            'stock_value' => (float) $summary->stock_value,
            'low_stock_count' => (int) $summary->low_stock_count,
            'out_of_stock_count' => (int) $summary->out_of_stock_count,
            'by_category' => $byCategory,
        ];
    }
}
