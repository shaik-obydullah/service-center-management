@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $supplier->name }}</h2>
            <span class="badge-{{ $supplier->status ? 'green' : 'slate' }}">{{ $supplier->status ? 'Active' : 'Inactive' }}</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary btn-sm">Edit</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Contact Information</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Contact Person</dt><dd>{{ $supplier->contact_person ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd>{{ $supplier->phone ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $supplier->email ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Address</dt><dd class="text-right">{{ $supplier->address ?: '-' }}</dd></div>
                </dl>
            </div>
            <div class="card p-5">
                <a href="{{ route('purchase-orders.create') }}" class="btn-primary w-full justify-center">+ New Purchase Order</a>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card overflow-hidden">
                <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Parts Supplied</h3>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th>Code</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Stock</th>
                                <th>Selling Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supplier->parts as $part)
                                <tr>
                                    <td class="font-mono text-xs text-slate-500">{{ $part->code }}</td>
                                    <td><a href="{{ route('parts.show', $part) }}" class="font-medium text-indigo-600 hover:underline">{{ $part->name }}</a></td>
                                    <td>{{ $part->brand ?: '-' }}</td>
                                    <td>{{ $part->quantity }}</td>
                                    <td>{{ format_money($part->selling_price) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-slate-400">No parts from this supplier yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card overflow-hidden">
                <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Purchase Orders</h3>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th>PO #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supplier->purchaseOrders as $po)
                                <tr>
                                    <td><a href="{{ route('purchase-orders.show', $po) }}" class="font-medium text-indigo-600 hover:underline">{{ $po->po_number }}</a></td>
                                    <td class="text-slate-500">{{ $po->order_date->format('M j, Y') }}</td>
                                    <td>{{ format_money($po->total_amount) }}</td>
                                    <td>
                                        @php $poStatus = \App\Enums\PurchaseOrderStatus::tryFrom($po->status); @endphp
                                        <span class="badge-{{ $po->status === 'received' ? 'green' : ($po->status === 'cancelled' ? 'red' : 'amber') }}">{{ $poStatus?->label() ?? $po->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">No purchase orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
