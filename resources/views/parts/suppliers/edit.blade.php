@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Edit {{ $supplier->name }}</h2>
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="input">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Address</label>
                        <textarea name="address" rows="2" class="input">{{ old('address', $supplier->address) }}</textarea>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="1" @selected(old('status', $supplier->status))>Active</option>
                            <option value="0" @selected(old('status', $supplier->status) === '0' || (old('status') === null && ! $supplier->status))>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
