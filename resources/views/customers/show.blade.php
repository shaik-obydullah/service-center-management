@extends('layouts.app')

@section('title', $customer->name)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $customer->name }}</h2>
            @if ($customer->loyalty_member)
                <span class="badge-green">Loyalty Member</span>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customers.devices.create', $customer) }}" class="btn-secondary btn-sm">+ Add Device</a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary btn-sm">Edit</a>
            <a href="{{ route('work-orders.create', ['customer_id' => $customer->id]) }}" class="btn-primary btn-sm">+ New Work Order</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Contact Information</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd>{{ $customer->phone }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $customer->email ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">City</dt><dd>{{ $customer->city ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Address</dt><dd class="text-right">{{ $customer->address ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Contact Pref.</dt><dd>{{ ucfirst($customer->contact_preference) }}</dd></div>
                </dl>
            </div>
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Stats</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Devices</dt><dd class="font-medium">{{ $customer->devices->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Total Orders</dt><dd class="font-medium">{{ $customer->workOrders->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Total Spent</dt><dd class="font-medium text-green-600">{{ format_money($customer->total_spent) }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="card mb-6">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-800">Devices ({{ $customer->devices->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead><tr class="border-b border-slate-200 bg-slate-50"><th>Device</th><th>Model</th><th>Serial</th><th>Color</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($customer->devices as $device)
                                <tr>
                                    <td class="font-medium">{{ $device->brand }}</td>
                                    <td>{{ $device->model }}</td>
                                    <td class="text-slate-500">{{ $device->serial_number ?: '-' }}</td>
                                    <td>{{ $device->color ?: '-' }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('customers.devices.edit', [$customer, $device]) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-slate-400">No devices registered.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-800">Work Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead><tr class="border-b border-slate-200 bg-slate-50"><th>Order</th><th>Device</th><th>Technician</th><th>Status</th><th>Invoice</th></tr></thead>
                        <tbody>
                            @forelse ($customer->workOrders as $workOrder)
                                <tr>
                                    <td><a href="{{ route('work-orders.show', $workOrder) }}" class="font-medium text-indigo-600 hover:underline">{{ $workOrder->order_number }}</a></td>
                                    <td class="text-slate-500">{{ $workOrder->device?->brand }} {{ $workOrder->device?->model }}</td>
                                    <td>{{ $workOrder->technician?->name ?: '-' }}</td>
                                    <td><span class="badge-{{ $workOrder->status }}">{{ \App\Enums\WorkOrderStatus::tryFrom($workOrder->status)?->label() ?? $workOrder->status }}</span></td>
                                    <td>
                                        @if ($workOrder->invoice)
                                            <a href="{{ route('invoices.show', $workOrder->invoice) }}" class="text-indigo-600 hover:underline">{{ $workOrder->invoice->invoice_number }}</a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-slate-400">No work orders.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
