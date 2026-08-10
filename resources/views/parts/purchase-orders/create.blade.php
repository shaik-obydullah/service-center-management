@extends('layouts.app')

@section('title', 'New Purchase Order')

@section('content')
    <div class="w-full">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Purchase Order</h2>
            <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Supplier *</label>
                        <select name="supplier_id" required class="input">
                            <option value="">Select supplier...</option>
                            @foreach ($suppliers as $id => $name)
                                <option value="{{ $id }}" @selected(old('supplier_id') == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Order Date *</label>
                        <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}" required class="input">
                        @error('order_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Items</label>
                    <div x-data="{
                        rows: [{ part_id: '', quantity: 1, unit_price: '' }],
                        addRow() { this.rows.push({ part_id: '', quantity: 1, unit_price: '' }); },
                        removeRow(index) { this.rows.splice(index, 1); },
                        get subtotal() {
                            return this.rows.reduce((sum, row) => sum + (Number(row.quantity) || 0) * (Number(row.unit_price) || 0), 0);
                        }
                    }">
                        <div class="space-y-2">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="flex gap-2">
                                    <select :name="`items[${index}][part_id]`" x-model="row.part_id" class="input" required>
                                        <option value="">Select part...</option>
                                        @foreach ($parts as $part)
                                            <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->code }})</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" :name="`items[${index}][quantity]`" x-model="row.quantity" class="input w-28" placeholder="Qty" required>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model="row.unit_price" class="input w-36" placeholder="Unit price" required>
                                    <button type="button" class="btn-danger btn-sm" x-show="rows.length > 1" @click="removeRow(index)">Remove</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" class="btn-secondary btn-sm mt-3" @click="addRow()">+ Add item</button>

                        <div class="mt-4 flex flex-wrap items-center justify-end gap-x-8 gap-y-2 border-t border-slate-200 pt-3 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-500">Items:</span>
                                <span class="font-semibold text-slate-700" x-text="rows.length"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-500">Subtotal:</span>
                                <span class="text-lg font-bold text-slate-800">
                                    <span class="mr-1">{{ setting('currency_symbol', '৳') }}</span><span x-text="subtotal.toFixed(2)"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    @error('items') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
@endsection
