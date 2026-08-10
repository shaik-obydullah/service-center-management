@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2" method="GET" action="{{ route('invoices.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice, order or customer..."
                   class="input w-72">
            <select name="status" class="input w-44" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach (\App\Enums\InvoiceStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') == $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('invoices.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Work Order</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        @php $status = \App\Enums\InvoiceStatus::tryFrom($invoice->status); @endphp
                        <tr class="hover:bg-slate-50">
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-indigo-600 hover:underline">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->workOrder?->customer?->name ?: '-' }}</td>
                            <td class="font-mono text-xs text-slate-500">{{ $invoice->workOrder?->order_number ?: '-' }}</td>
                            <td>{{ format_money($invoice->total) }}</td>
                            <td class="text-green-600">{{ format_money($invoice->paid_amount) }}</td>
                            <td>{{ format_money($invoice->balance_due) }}</td>
                            <td>
                                <span class="badge-{{ $invoice->status === 'paid' ? 'green' : ($invoice->status === 'cancelled' ? 'red' : 'amber') }}">{{ $status?->label() ?? $invoice->status }}</span>
                            </td>
                            <td class="text-slate-500">{{ $invoice->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
@endsection
