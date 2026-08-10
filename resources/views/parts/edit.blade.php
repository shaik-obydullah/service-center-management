@extends('layouts.app')

@section('title', 'Edit Part')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Edit {{ $part->code }}</h2>
            <form method="POST" action="{{ route('parts.update', $part) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $part->name) }}" required class="input">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Code *</label>
                        <input type="text" name="code" value="{{ old('code', $part->code) }}" required class="input">
                        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Category</label>
                        <select name="part_category_id" class="input">
                            <option value="">Select category...</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected(old('part_category_id', $part->part_category_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Supplier</label>
                        <select name="supplier_id" class="input">
                            <option value="">Select supplier...</option>
                            @foreach ($suppliers as $id => $name)
                                <option value="{{ $id }}" @selected(old('supplier_id', $part->supplier_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $part->brand) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Model</label>
                        <input type="text" name="model" value="{{ old('model', $part->model) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Cost Price *</label>
                        <input type="number" name="cost_price" step="0.01" min="0" value="{{ old('cost_price', $part->cost_price) }}" required class="input">
                        @error('cost_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Selling Price *</label>
                        <input type="number" name="selling_price" step="0.01" min="0" value="{{ old('selling_price', $part->selling_price) }}" required class="input">
                        @error('selling_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Minimum Stock *</label>
                        <input type="number" name="minimum_stock" min="0" value="{{ old('minimum_stock', $part->minimum_stock) }}" required class="input">
                        @error('minimum_stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="active" @selected(old('status', $part->status) == 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $part->status) == 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Current Stock</label>
                        <input type="text" value="{{ $part->quantity }}" disabled class="input bg-slate-50 text-slate-500">
                        <p class="mt-1 text-xs text-slate-400">Adjust stock on the part page.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('parts.show', $part) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
