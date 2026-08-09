@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-slate-500">Active Work Orders</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['active_work_orders'] }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['work_orders'] }} total</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Customers</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['customers'] }}</p>
            <a href="{{ route('customers.index') }}" class="mt-1 inline-block text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Revenue Collected</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ format_money($stats['revenue']) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['unpaid_invoices'] }} unpaid invoices</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Parts Inventory</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['parts'] }}</p>
            @if ($stats['low_stock_parts'] > 0)
                <a href="{{ route('parts.index', ['filter' => 'low']) }}" class="mt-1 inline-block text-xs font-medium text-red-600 hover:underline">{{ $stats['low_stock_parts'] }} low stock</a>
            @else
                <p class="mt-1 text-xs text-slate-400">All stocked up</p>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Recent Work Orders</h2>
                <a href="{{ route('work-orders.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Device</th>
                            <th>Technician</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentWorkOrders as $workOrder)
                            <tr>
                                <td class="font-medium text-indigo-600"><a href="{{ route('work-orders.show', $workOrder) }}">{{ $workOrder->order_number }}</a></td>
                                <td>{{ $workOrder->customer?->name }}</td>
                                <td class="text-slate-500">{{ $workOrder->device?->brand }} {{ $workOrder->device?->model }}</td>
                                <td>{{ $workOrder->technician?->name ?: '-' }}</td>
                                <td><span class="badge-{{ $workOrder->status }}">{{ \App\Enums\WorkOrderStatus::tryFrom($workOrder->status)?->label() ?? $workOrder->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-400">No work orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5">
                <h2 class="mb-4 font-semibold text-slate-800">Work Orders by Status</h2>
                <div class="space-y-3">
                    @foreach (\App\Enums\WorkOrderStatus::cases() as $status)
                        @php
                            $count = $statusCounts->get($status->value, 0);
                            $total = max(1, $statusCounts->sum());
                            $width = round(($count / $total) * 100);
                        @endphp
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-slate-600">{{ $status->label() }}</span>
                                <span class="font-medium text-slate-800">{{ $count }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-{{ $status->color() }}-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card p-5">
                <h2 class="mb-4 font-semibold text-slate-800">Technician Workload</h2>
                @forelse ($workload as $tech)
                    <div class="mb-3 flex items-center justify-between text-sm">
                        <span class="text-slate-600">{{ $tech->name }}</span>
                        <span class="badge-amber">{{ $tech->active }} active</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No active assignments.</p>
                @endforelse
            </div>

            <div class="card p-5">
                <h2 class="mb-4 font-semibold text-slate-800">New Customers</h2>
                <ul class="space-y-3">
                    @forelse ($recentCustomers as $customer)
                        <li class="flex items-center justify-between">
                            <a href="{{ route('customers.show', $customer) }}" class="text-sm font-medium text-slate-700 hover:text-indigo-600">{{ $customer->name }}</a>
                            <span class="text-xs text-slate-400">{{ $customer->work_orders_count }} orders</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">No customers yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
