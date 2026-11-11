<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTechnicianRequest;
use App\Http\Requests\UpdateTechnicianRequest;
use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $technicians = Technician::query()
            ->withCount('workOrders')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('employee_id', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($technicians);
    }

    public function store(StoreTechnicianRequest $request)
    {
        $data = $request->validated();
        $data['skills_json'] = $data['skills_json'] ?? [];

        $technician = Technician::create($data);

        return response()->json(['message' => 'Technician created.', 'technician' => $technician], 201);
    }

    public function show(Technician $technician)
    {
        $technician->load('user', 'workOrders');

        return response()->json($technician);
    }

    public function update(UpdateTechnicianRequest $request, Technician $technician)
    {
        $data = $request->validated();
        $data['skills_json'] = $data['skills_json'] ?? [];

        $technician->update($data);

        return response()->json(['message' => 'Technician updated.', 'technician' => $technician->fresh()]);
    }

    public function workOrders(Technician $technician)
    {
        return response()->json(
            $technician->workOrders()->with('customer', 'device')->latest()->get()
        );
    }

    public function performance(Request $request, Technician $technician)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->toDateString();

        $orders = $technician->workOrders()
            ->with('customer')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalHours = $technician->assignments()
            ->whereBetween('assigned_at', [$from, $to])
            ->sum('hours_spent');

        return response()->json([
            'technician' => $technician,
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'total_hours' => (float) $totalHours,
            'revenue' => (float) $orders->where('status', 'completed')->sum('actual_cost'),
            'orders' => $orders,
        ]);
    }
}
