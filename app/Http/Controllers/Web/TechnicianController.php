<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTechnicianRequest;
use App\Http\Requests\UpdateTechnicianRequest;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $technicians = Technician::query()
            ->withCount('workOrders')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('employee_id', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('technicians.index', compact('technicians'));
    }

    public function create()
    {
        $users = User::doesntHave('technician')->get();

        return view('technicians.create', compact('users'));
    }

    public function store(StoreTechnicianRequest $request)
    {
        $data = $request->validated();
        $data['skills_json'] = $data['skills_json'] ?? [];

        Technician::create($data);

        return redirect()
            ->route('technicians.index')
            ->with('success', 'Technician created successfully.');
    }

    public function show(Technician $technician)
    {
        $technician->load(['workOrders.customer', 'workOrders.device', 'workOrders.invoice']);

        $technician->loadCount([
            'workOrders as total_orders',
            'workOrders as completed_orders' => fn ($q) => $q->where('status', 'completed'),
        ]);

        $totalRevenue = $technician->workOrders()
            ->where('status', 'completed')
            ->sum('actual_cost');

        return view('technicians.show', compact('technician', 'totalRevenue'));
    }

    public function edit(Technician $technician)
    {
        $users = User::whereDoesntHave('technician')->orWhereHas('technician', fn ($q) => $q->where('id', $technician->id))->get();

        return view('technicians.edit', compact('technician', 'users'));
    }

    public function update(UpdateTechnicianRequest $request, Technician $technician)
    {
        $data = $request->validated();
        $data['skills_json'] = $data['skills_json'] ?? [];

        $technician->update($data);

        return redirect()
            ->route('technicians.show', $technician)
            ->with('success', 'Technician updated successfully.');
    }

    public function destroy(Technician $technician)
    {
        if ($technician->workOrders()->exists()) {
            return redirect()
                ->route('technicians.index')
                ->with('error', 'Cannot delete a technician with work orders.');
        }

        $technician->delete();

        return redirect()
            ->route('technicians.index')
            ->with('success', 'Technician deleted successfully.');
    }
}
