@extends('layouts.app')

@section('title', 'Warranties')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2" method="GET" action="{{ route('warranties.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order, customer or part..."
                   class="input w-72">
            <select name="status" class="input w-44" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') == 'active')>Active</option>
                <option value="expired" @selected(request('status') == 'expired')>Expired</option>
                <option value="revoked" @selected(request('status') == 'revoked')>Revoked</option>
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('warranties.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Work Order</th>
                        <th>Customer</th>
                        <th>Part</th>
                        <th>Duration</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warranties as $warranty)
                        <tr class="hover:bg-slate-50">
                            <td><a href="{{ route('warranties.show', $warranty) }}" class="font-mono text-xs font-medium text-indigo-600 hover:underline">{{ $warranty->workOrder?->order_number ?: '-' }}</a></td>
                            <td class="font-medium">{{ $warranty->workOrder?->customer?->name ?: '-' }}</td>
                            <td>{{ $warranty->part?->name ?: '-' }}</td>
                            <td>{{ $warranty->duration_days }} days</td>
                            <td class="text-slate-500">{{ $warranty->end_date->format('M j, Y') }}</td>
                            <td><span class="badge-{{ $warranty->status_badge }}">{{ $warranty->status_label }}</span></td>
                            <td class="text-slate-500">{{ $warranty->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No warranties found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $warranties->links() }}
    </div>
@endsection
