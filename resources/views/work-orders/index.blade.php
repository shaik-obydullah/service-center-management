@extends('layouts.app')

@section('title', 'Work Orders')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex flex-wrap gap-2" method="GET" action="{{ route('work-orders.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order #, customer..."
                   class="input w-64">
            <select name="status" class="input w-44" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            @if (request()->hasAny(['search', 'status', 'priority']))
                <a href="{{ route('work-orders.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
        <a href="{{ route('work-orders.create') }}" class="btn-primary">+ New Work Order</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Device</th>
                        <th>Technician</th>
                        <th>Priority</th>
                        <th>Est. Cost</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workOrders as $workOrder)
                        <tr class="hover:bg-slate-50">
                            <td>
                                <a href="{{ route('work-orders.show', $workOrder) }}" class="font-medium text-indigo-600 hover:underline">{{ $workOrder->order_number }}</a>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $workOrder->customer) }}" class="font-medium text-slate-700 hover:text-indigo-600">{{ $workOrder->customer->name }}</a>
                            </td>
                            <td class="text-slate-500">
                                @if ($workOrder->device)
                                    {{ $workOrder->device->brand }} {{ $workOrder->device->model }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $workOrder->technician?->name ?: '-' }}</td>
                            <td>
                                @php $priority = \App\Enums\WorkOrderPriority::tryFrom($workOrder->priority); @endphp
                                <span class="badge-{{ $priority?->color() ?? 'slate' }}">{{ $priority?->label() ?? $workOrder->priority }}</span>
                            </td>
                            <td>{{ format_money($workOrder->estimated_cost) }}</td>
                            <td>
                                @php $status = \App\Enums\WorkOrderStatus::tryFrom($workOrder->status); @endphp
                                <span class="badge-{{ $status?->color() ?? 'slate' }}">{{ $status?->label() ?? $workOrder->status }}</span>
                            </td>
                            <td class="text-slate-500">{{ $workOrder->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">No work orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $workOrders->links() }}
    </div>
@endsection
