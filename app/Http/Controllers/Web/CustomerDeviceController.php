<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\DeviceType;

class CustomerDeviceController extends Controller
{
    public function create(Customer $customer)
    {
        $deviceTypes = DeviceType::where('status', true)->pluck('name', 'id');

        return view('customers.devices.create', compact('customer', 'deviceTypes'));
    }

    public function store(StoreDeviceRequest $request, Customer $customer)
    {
        $customer->devices()->create($request->validated());

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Device added successfully.');
    }

    public function edit(Customer $customer, CustomerDevice $device)
    {
        $deviceTypes = DeviceType::where('status', true)->pluck('name', 'id');

        return view('customers.devices.edit', compact('customer', 'device', 'deviceTypes'));
    }

    public function update(StoreDeviceRequest $request, Customer $customer, CustomerDevice $device)
    {
        $device->update($request->validated());

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Customer $customer, CustomerDevice $device)
    {
        if ($device->workOrders()->exists()) {
            return redirect()
                ->route('customers.show', $customer)
                ->with('error', 'Cannot delete a device with work orders.');
        }

        $device->delete();

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Device deleted successfully.');
    }
}
