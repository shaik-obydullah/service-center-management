@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2" method="GET" action="{{ route('purchase-orders.index') }}">
            <select name="status" class="input w-44" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if (request('status'))
                <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
        <a href="{{ route('purchase-orders.create') }}" class="btn-primary">+ New Purchase Order</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50">
                            <td><a href="{{ route('purchase-orders.show', $po) }}" class="font-medium text-indigo-600 hover:underline">{{ $po->po_number }}</a></td>
                            <td>{{ $po->supplier->name }}</td>
                            <td class="text-slate-500">{{ $po->order_date->format('M j, Y') }}</td>
                            <td>{{ $po->items_count ?? $po->items->count() }}</td>
                            <td>{{ format_money($po->total_amount) }}</td>
                            <td>
                                @php $status = \App\Enums\PurchaseOrderStatus::tryFrom($po->status); @endphp
                                <span class="badge-{{ $po->status === 'received' ? 'green' : ($po->status === 'cancelled' ? 'red' : 'amber') }}">{{ $status?->label() ?? $po->status }}</span>
                            </td>
                            <td class="text-slate-500">{{ $po->creator?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No purchase orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $purchaseOrders->links() }}
    </div>
@endsection
