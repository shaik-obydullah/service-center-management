<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        $type = $request->type ?? 'revenue';
        $from = $request->from;
        $to = $request->to;

        $data = match ($type) {
            'technicians' => $reports->technicianPerformance($from, $to),
            'popular-repairs' => $reports->popularRepairs($from, $to),
            'inventory' => $reports->inventory(),
            default => $reports->revenue($from, $to),
        };

        return view('reports.index', compact('type', 'from', 'to', 'data'));
    }
}
