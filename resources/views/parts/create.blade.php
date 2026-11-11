@extends('layouts.app')

@section('title', 'Add Part')

@section('content')
    <div class="w-full">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Part Information</h2>
            <form method="POST" action="{{ route('parts.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Code *</label>
                        <input type="text" name="code" value="{{ old('code') }}" required class="input" placeholder="e.g. SCR-001">
                        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Category</label>
                        <select name="part_category_id" class="input">
                            <option value="">Select category...</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected(old('part_category_id') == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Supplier</label>
                        <select name="supplier_id" class="input">
                            <option value="">Select supplier...</option>
                            @foreach ($suppliers as $id => $name)
                                <option value="{{ $id }}" @selected(old('supplier_id') == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Model</label>
                        <input type="text" name="model" value="{{ old('model') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Cost Price *</label>
                        <input type="number" name="cost_price" step="0.01" min="0" value="{{ old('cost_price') }}" required class="input">
                        @error('cost_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Selling Price *</label>
                        <input type="number" name="selling_price" step="0.01" min="0" value="{{ old('selling_price') }}" required class="input">
                        @error('selling_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Opening Stock *</label>
                        <input type="number" name="quantity" min="0" value="{{ old('quantity', 0) }}" required class="input">
                        @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Minimum Stock *</label>
                        <input type="number" name="minimum_stock" min="0" value="{{ old('minimum_stock', 0) }}" required class="input">
                        @error('minimum_stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="active" @selected(old('status', 'active') == 'active')>Active</option>
                            <option value="inactive" @selected(old('status') == 'inactive')>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('parts.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Part</button>
                </div>
            </form>
        </div>
    </div>
@endsection
