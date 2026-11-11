@extends('layouts.app')

@section('title', 'Warranty #' . $warranty->id)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('warranties.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">Warranty #{{ $warranty->id }}</h2>
            <span class="badge-{{ $warranty->status_badge }}">{{ $warranty->status_label }}</span>
        </div>
        @if ($warranty->status === 'active')
            <form method="POST" action="{{ route('warranties.revoke', $warranty) }}" onsubmit="return confirm('Revoke this warranty? This cannot be undone.');">
                @csrf
                <button type="submit" class="btn-danger btn-sm">Revoke Warranty</button>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card p-5">
            <h3 class="mb-3 font-semibold text-slate-800">Coverage</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Work Order</dt>
                    <dd><a href="{{ route('work-orders.show', $warranty->workOrder) }}" class="font-mono text-indigo-600 hover:underline">{{ $warranty->workOrder?->order_number }}</a></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Part</dt>
                    <dd>{{ $warranty->part?->name ?: 'All (service)' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Duration</dt>
                    <dd>{{ $warranty->duration_days }} days</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Start Date</dt>
                    <dd>{{ $warranty->start_date->format('M j, Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">End Date</dt>
                    <dd class="font-semibold">{{ $warranty->end_date->format('M j, Y') }}</dd>
                </div>
                @if (!$warranty->is_expired && $warranty->status !== 'revoked')
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Days Remaining</dt>
                        <dd class="font-semibold text-green-600">{{ $warranty->remaining_days }} days</dd>
                    </div>
                @endif
                @if ($warranty->notes)
                    <div class="pt-2">
                        <dt class="text-slate-500">Notes</dt>
                        <dd class="mt-1 text-slate-700">{{ $warranty->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="card p-5 lg:col-span-2">
            <h3 class="mb-3 font-semibold text-slate-800">Customer</h3>
            <div class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <p class="text-slate-500">Name</p>
                    <p class="font-medium">
                        <a href="{{ route('customers.show', $warranty->workOrder?->customer) }}" class="text-indigo-600 hover:underline">{{ $warranty->workOrder?->customer?->name ?: '-' }}</a>
                    </p>
                </div>
                <div>
                    <p class="text-slate-500">Phone</p>
                    <p class="font-medium">{{ $warranty->workOrder?->customer?->phone ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Device</p>
                    <p class="font-medium">{{ $warranty->workOrder?->device ? ($warranty->workOrder->device->brand . ' ' . $warranty->workOrder->device->model) : '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Serial Number</p>
                    <p class="font-medium">{{ $warranty->workOrder?->device?->serial_number ?: '-' }}</p>
                </div>
            </div>

            @if ($warranty->status === 'active')
                <div class="mt-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                    Revoking this warranty permanently ends coverage for this work order.
                </div>
            @elseif ($warranty->status === 'revoked')
                <div class="mt-6 rounded-lg bg-slate-100 p-4 text-sm text-slate-600">
                    This warranty was revoked and no longer provides coverage.
                </div>
            @else
                <div class="mt-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                    This warranty expired on {{ $warranty->end_date->format('M j, Y') }}.
                </div>
            @endif
        </div>
    </div>
@endsection
