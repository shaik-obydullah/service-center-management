@extends('layouts.app')

@section('title', 'Generate Invoice')

@section('content')
    <div class="w-full">
        <div class="mb-4 flex items-center gap-3">
            <a href="{{ route('work-orders.show', $workOrder) }}" class="btn-secondary btn-sm">← Back to Work Order</a>
            <h2 class="text-xl font-semibold text-slate-800">Generate Invoice</h2>
        </div>

        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">
                {{ $workOrder->order_number }} — {{ $workOrder->customer->name }}
            </h3>
            <div class="p-5">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Service Charge</dt><dd>{{ format_money($breakdown['service_charge']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Parts</dt><dd>{{ format_money($breakdown['parts_cost']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ format_money($breakdown['subtotal']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Discount</dt><dd class="text-red-600">-{{ format_money($breakdown['discount']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tax ({{ setting('tax_rate', 0) }}%)</dt><dd>{{ format_money($breakdown['tax']) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-semibold">
                        <dt>Total</dt><dd>{{ format_money($breakdown['total']) }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('invoices.store', $workOrder) }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="label">Service Charge Override</label>
                        <input type="number" name="service_charge" step="0.01" min="0" value="{{ old('service_charge', $breakdown['service_charge']) }}" class="input">
                        <p class="mt-1 text-xs text-slate-400">Leave unchanged to use the work order estimate.</p>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Generate Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
