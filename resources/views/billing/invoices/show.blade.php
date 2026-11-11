@extends('layouts.app')

@section('title', $invoice->invoice_number)

@section('content')
    @php $status = \App\Enums\InvoiceStatus::tryFrom($invoice->status); @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $invoice->invoice_number }}</h2>
            <span class="badge-{{ $invoice->status === 'paid' ? 'green' : ($invoice->status === 'cancelled' ? 'red' : 'amber') }}">{{ $status?->label() ?? $invoice->status }}</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('exports.invoice-pdf', $invoice) }}" class="btn-secondary btn-sm">Download PDF</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $invoice->workOrder->customer->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd>{{ $invoice->workOrder->customer->phone }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Device</dt><dd>{{ $invoice->workOrder->device?->brand }} {{ $invoice->workOrder->device?->model }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Work Order</dt><dd><a href="{{ route('work-orders.show', $invoice->workOrder) }}" class="text-indigo-600 hover:underline">{{ $invoice->workOrder->order_number }}</a></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Date</dt><dd>{{ $invoice->created_at->format('M j, Y g:i A') }}</dd></div>
                </dl>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Totals</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Service Charge</dt><dd>{{ format_money($invoice->service_charge) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Parts</dt><dd>{{ format_money($invoice->parts_cost) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ format_money($invoice->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Discount</dt><dd class="text-red-600">-{{ format_money($invoice->discount) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tax</dt><dd>{{ format_money($invoice->tax) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-2 font-semibold"><dt>Total</dt><dd>{{ format_money($invoice->total) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Paid</dt><dd class="text-green-600">{{ format_money($invoice->paid_amount) }}</dd></div>
                    <div class="flex justify-between font-semibold"><dt>Balance Due</dt><dd class="text-red-600">{{ format_money($invoice->balance_due) }}</dd></div>
                </dl>
            </div>

            @if ($invoice->balance_due > 0)
                <div class="card p-5">
                    <h3 class="mb-4 font-semibold text-slate-800">Record Payment</h3>
                    <form method="POST" action="{{ route('invoices.pay', $invoice) }}" class="space-y-3">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->balance_due }}" placeholder="Amount" required class="input">
                        <select name="method" class="input" required>
                            @foreach ($methods as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'cash')>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="reference" placeholder="Reference (optional)" class="input">
                        <button type="submit" class="btn-primary w-full justify-center">Record Payment</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="font-semibold text-slate-800">Line Items</h3>
                <span class="text-xs text-slate-400">Parts used on work order</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Part</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->workOrder->partUsages as $usage)
                            <tr>
                                <td class="font-medium">{{ $usage->part->name }}</td>
                                <td>{{ $usage->quantity }}</td>
                                <td>{{ format_money($usage->unit_price) }}</td>
                                <td>{{ format_money($usage->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-slate-400">No parts on this invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100">
                <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Payments</h3>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Received By</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoice->payments as $payment)
                                <tr>
                                    <td class="font-medium">{{ format_money($payment->amount) }}</td>
                                    <td>
                                        @php $method = \App\Enums\PaymentMethod::tryFrom($payment->method); @endphp
                                        {{ $method?->label() ?? $payment->method }}
                                    </td>
                                    <td class="text-slate-500">{{ $payment->reference ?: '-' }}</td>
                                    <td>
                                        <span class="badge-{{ $payment->status === 'completed' ? 'green' : 'red' }}">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                    <td>{{ $payment->receiver?->name ?: '-' }}</td>
                                    <td class="text-slate-500">{{ $payment->created_at->format('M j, Y g:i A') }}</td>
                                    <td>
                                        @if ($payment->status === 'completed')
                                            <form method="POST" action="{{ route('payments.refund', $payment) }}"
                                                  onsubmit="return confirm('Refund this payment of {{ format_money($payment->amount) }}?')">
                                                @csrf
                                                <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Refund</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-6 text-center text-slate-400">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
