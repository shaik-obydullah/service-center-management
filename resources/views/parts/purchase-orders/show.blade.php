@extends('layouts.app')

@section('title', $purchaseOrder->po_number)

@section('content')
    @php $status = \App\Enums\PurchaseOrderStatus::tryFrom($purchaseOrder->status); @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $purchaseOrder->po_number }}</h2>
            <span class="badge-{{ $purchaseOrder->status === 'received' ? 'green' : ($purchaseOrder->status === 'cancelled' ? 'red' : 'amber') }}">{{ $status?->label() ?? $purchaseOrder->status }}</span>
        </div>
        @if ($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Pending->value)
            <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
                @csrf
                <button type="submit" class="btn-primary btn-sm">Receive & Restock</button>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Supplier</dt><dd class="font-medium">{{ $purchaseOrder->supplier->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Order Date</dt><dd>{{ $purchaseOrder->order_date->format('M j, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Created By</dt><dd>{{ $purchaseOrder->creator?->name ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Total Amount</dt><dd class="font-semibold">{{ format_money($purchaseOrder->total_amount) }}</dd></div>
                </dl>
            </div>
            @if ($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Pending->value)
                <div class="card p-5">
                    <p class="text-sm text-slate-600">
                        Receiving this order will add each item's quantity to inventory and mark it as received.
                    </p>
                </div>
            @endif
        </div>

        <div class="card overflow-hidden lg:col-span-2">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Items</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Part</th>
                            <th>Code</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="font-medium">
                                    <a href="{{ route('parts.show', $item->part) }}" class="text-indigo-600 hover:underline">{{ $item->part->name }}</a>
                                </td>
                                <td class="font-mono text-xs text-slate-500">{{ $item->part->code }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ format_money($item->unit_price) }}</td>
                                <td>{{ format_money($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-semibold">
                            <td colspan="4" class="text-right">Total</td>
                            <td>{{ format_money($purchaseOrder->total_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
