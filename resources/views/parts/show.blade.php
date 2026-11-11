@extends('layouts.app')

@section('title', $part->name)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('parts.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $part->name }}</h2>
            <span class="font-mono text-sm text-slate-400">{{ $part->code }}</span>
            <span class="badge-{{ $part->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($part->status) }}</span>
            @if ($part->is_low_stock)
                <span class="badge-amber">Low Stock</span>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('parts.edit', $part) }}" class="btn-secondary btn-sm">Edit</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-4 font-semibold text-slate-800">Stock Overview</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">In Stock</dt>
                        <dd class="font-medium {{ $part->quantity <= $part->minimum_stock ? 'text-red-600' : '' }}">{{ $part->quantity }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Minimum Stock</dt>
                        <dd class="font-medium">{{ $part->minimum_stock }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Stock Value</dt>
                        <dd class="font-medium">{{ format_money($part->stock_value) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Cost Price</dt>
                        <dd class="font-medium">{{ format_money($part->cost_price) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Selling Price</dt>
                        <dd class="font-medium">{{ format_money($part->selling_price) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Category</dt>
                        <dd class="font-medium">{{ $part->partCategory?->name ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Supplier</dt>
                        <dd class="font-medium">{{ $part->supplier?->name ?: '-' }}</dd>
                    </div>
                    @if ($part->brand)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Brand</dt>
                            <dd class="font-medium">{{ $part->brand }}</dd>
                        </div>
                    @endif
                    @if ($part->model)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Model</dt>
                            <dd class="font-medium">{{ $part->model }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="card p-5">
                <h3 class="mb-4 font-semibold text-slate-800">Restock</h3>
                <form method="POST" action="{{ route('parts.restock', $part) }}" class="space-y-3">
                    @csrf
                    <input type="number" name="quantity" min="1" placeholder="Quantity" required class="input">
                    <input type="text" name="notes" placeholder="Notes (optional)" class="input">
                    <button type="submit" class="btn-primary w-full justify-center">Receive Stock</button>
                </form>
            </div>

            <div class="card p-5">
                <h3 class="mb-4 font-semibold text-slate-800">Adjust Stock</h3>
                <form method="POST" action="{{ route('parts.adjust', $part) }}" class="space-y-3">
                    @csrf
                    <input type="number" name="delta" placeholder="Delta (+/-)" required class="input">
                    <input type="text" name="reason" placeholder="Reason (optional)" class="input">
                    <button type="submit" class="btn-secondary w-full justify-center">Apply Adjustment</button>
                </form>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card overflow-hidden">
                <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Stock Movements</h3>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($part->movements as $movement)
                                <tr>
                                    <td>
                                        @php $mt = \App\Enums\StockMovementType::tryFrom($movement->type); @endphp
                                        <span class="badge-{{ $movement->type === 'in' ? 'green' : ($movement->type === 'out' ? 'red' : 'amber') }}">{{ $mt?->label() ?? $movement->type }}</span>
                                    </td>
                                    <td class="font-medium">{{ $movement->type === 'out' ? '-' : '' }}{{ $movement->quantity }}</td>
                                    <td class="font-mono text-xs text-slate-500">{{ $movement->reference ?: '-' }}</td>
                                    <td class="text-slate-500">{{ $movement->notes ?: '-' }}</td>
                                    <td>{{ $movement->user?->name ?: '-' }}</td>
                                    <td class="text-slate-500">{{ $movement->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-6 text-center text-slate-400">No stock movements yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card overflow-hidden">
                <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Usage History</h3>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th>Work Order</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($part->usage as $usage)
                                <tr>
                                    <td>
                                        <a href="{{ route('work-orders.show', $usage->workOrder) }}" class="font-medium text-indigo-600 hover:underline">{{ $usage->workOrder->order_number }}</a>
                                    </td>
                                    <td>{{ $usage->quantity }}</td>
                                    <td>{{ format_money($usage->unit_price) }}</td>
                                    <td>{{ format_money($usage->total) }}</td>
                                    <td class="text-slate-500">{{ $usage->created_at->format('M j, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-slate-400">This part has not been used yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
