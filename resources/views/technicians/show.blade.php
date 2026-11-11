@extends('layouts.app')

@section('title', $technician->name)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('technicians.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $technician->name }}</h2>
            <span class="font-mono text-sm text-slate-400">{{ $technician->employee_id }}</span>
            <span class="badge-{{ $technician->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($technician->status) }}</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('technicians.edit', $technician) }}" class="btn-secondary btn-sm">Edit</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Performance</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Total Orders</dt><dd class="font-medium">{{ $technician->total_orders }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Completed</dt><dd class="font-medium text-green-600">{{ $technician->completed_orders }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Active Now</dt><dd class="font-medium">{{ $technician->active_work_orders_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Revenue Generated</dt><dd class="font-medium">{{ format_money($totalRevenue) }}</dd></div>
                </dl>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Contact</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd>{{ $technician->phone }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $technician->email ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Hourly Rate</dt><dd>{{ format_money($technician->hourly_rate) }}</dd></div>
                    @if ($technician->user)
                        <div class="flex justify-between"><dt class="text-slate-500">User Account</dt><dd>{{ $technician->user->email }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Skills</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse ($technician->skills_json ?? [] as $skill)
                        <span class="badge-indigo">{{ $skill }}</span>
                    @empty
                        <p class="text-sm text-slate-400">No skills listed.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card overflow-hidden lg:col-span-2">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Work Orders</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Device</th>
                            <th>Est. Cost</th>
                            <th>Invoice</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($technician->workOrders as $workOrder)
                            <tr>
                                <td><a href="{{ route('work-orders.show', $workOrder) }}" class="font-medium text-indigo-600 hover:underline">{{ $workOrder->order_number }}</a></td>
                                <td>{{ $workOrder->customer?->name }}</td>
                                <td class="text-slate-500">{{ $workOrder->device?->brand }} {{ $workOrder->device?->model }}</td>
                                <td>{{ format_money($workOrder->estimated_cost) }}</td>
                                <td>
                                    @if ($workOrder->invoice)
                                        <a href="{{ route('invoices.show', $workOrder->invoice) }}" class="text-indigo-600 hover:underline">{{ $workOrder->invoice->invoice_number }}</a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php $status = \App\Enums\WorkOrderStatus::tryFrom($workOrder->status); @endphp
                                    <span class="badge-{{ $status?->color() ?? 'slate' }}">{{ $status?->label() ?? $workOrder->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-400">No work orders assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
