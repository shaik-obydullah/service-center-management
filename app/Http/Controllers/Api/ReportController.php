<?php

namespace App\Http\Controllers\Api;

use App\Exports\GenericDataExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function revenue(Request $request, ReportService $reports)
    {
        return response()->json($reports->revenue($request->from, $request->to));
    }

    public function technicians(Request $request, ReportService $reports)
    {
        return response()->json($reports->technicianPerformance($request->from, $request->to));
    }

    public function popularRepairs(Request $request, ReportService $reports)
    {
        return response()->json($reports->popularRepairs($request->from, $request->to));
    }

    public function inventory(Request $request, ReportService $reports)
    {
        return response()->json($reports->inventory());
    }

    public function export(Request $request, ReportService $reports)
    {
        $type = $request->type ?? 'revenue';
        $format = $request->format ?? 'xlsx';
        $from = $request->from;
        $to = $request->to;

        [$headings, $rows] = match ($type) {
            'technicians' => [
                ['Name', 'Employee ID', 'Total Orders', 'Completed', 'Revenue', 'Hours'],
                $reports->technicianPerformance($from, $to)
                    ->map(fn ($r) => [$r->name, $r->employee_id, $r->total_orders, $r->completed_orders, $r->revenue, $r->total_hours]),
            ],
            'popular-repairs' => [
                ['Repair Service', 'Count'],
                $reports->popularRepairs($from, $to)->map(fn ($r) => [$r->name, $r->total_count]),
            ],
            default => [
                ['Date', 'Billed', 'Collected'],
                $reports->revenue($from, $to)['daily']
                    ->map(fn ($r) => [$r->date, $r->total, $r->total]),
            ],
        };

        $export = new GenericDataExport(collect($rows), $headings);
        $filename = "{$type}-report-" . now()->format('Y-m-d') . ".{$format}";

        return Excel::download($export, $filename);
    }
}
