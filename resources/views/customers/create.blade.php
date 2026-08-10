@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
    <div class="w-full">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Customer Information</h2>
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required class="input">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input">
                    </div>
                    <div>
                        <label class="label">NID Number</label>
                        <input type="text" name="nid_number" value="{{ old('nid_number') }}" class="input">
                    </div>
                    <div>
                        <label class="label">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Contact Preference</label>
                        <select name="contact_preference" class="input">
                            @foreach (['phone' => 'Phone', 'email' => 'Email', 'sms' => 'SMS'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('contact_preference', 'phone') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Address</label>
                        <textarea name="address" rows="2" class="input">{{ old('address') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="loyalty_member" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('loyalty_member'))>
                            Loyalty program member
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('customers.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
@endsection
