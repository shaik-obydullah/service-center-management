@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
    <div class="w-full">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Supplier Information</h2>
            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Address</label>
                        <textarea name="address" rows="2" class="input">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="1" @selected(old('status', true))>Active</option>
                            <option value="0" @selected(old('status') === '0')>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Supplier</button>
                </div>
            </form>
        </div>
    </div>
@endsection
