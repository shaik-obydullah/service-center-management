<?php

namespace App\Http\Controllers\Web;

use App\Exports\GenericDataExport;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function invoicePdf(Invoice $invoice)
    {
        $invoice->load('workOrder.customer', 'workOrder.device', 'workOrder.partUsages.part', 'workOrder.technician');

        $pdf = Pdf::loadView('billing.invoices.pdf', compact('invoice'))
            ->setPaper('a4');

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    public function report(Request $request, ReportService $reports): BinaryFileResponse
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
            'inventory' => [
                ['Category', 'Parts', 'Total Quantity'],
                $reports->inventory()['by_category']->map(fn ($r) => [$r->name, $r->part_count, $r->total_quantity]),
            ],
            default => [
                ['Date', 'Invoices', 'Billed', 'Collected'],
                $reports->revenue($from, $to)['daily']
                    ->map(fn ($r) => [$r->date, $r->invoice_count ?? 0, $r->total, $r->total]),
            ],
        };

        $export = new GenericDataExport(collect($rows), $headings);

        $filename = str_replace('_', '-', "{$type}-report-") . now()->format('Y-m-d') . ".{$format}";

        return Excel::download($export, $filename);
    }
}
