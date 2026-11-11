<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('workOrders', 'devices')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($customers);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return response()->json(['message' => 'Customer created.', 'customer' => $customer], 201);
    }

    public function show(Customer $customer)
    {
        $customer->load('devices', 'workOrders');

        return response()->json($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return response()->json(['message' => 'Customer updated.', 'customer' => $customer->fresh()]);
    }

    public function devices(Customer $customer)
    {
        return response()->json($customer->devices);
    }

    public function storeDevice(StoreDeviceRequest $request, Customer $customer)
    {
        $device = $customer->devices()->create($request->validated());

        return response()->json(['message' => 'Device added.', 'device' => $device], 201);
    }

    public function workOrders(Customer $customer)
    {
        return response()->json(
            $customer->workOrders()->with('device', 'technician', 'invoice')->latest()->get()
        );
    }
}
