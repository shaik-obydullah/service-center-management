@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">General Settings</h2>
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Shop Name</label>
                        <input type="text" name="shop_name" value="{{ old('shop_name', setting('shop_name', config('app.name'))) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', setting('currency_symbol', '৳')) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', setting('phone')) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email', setting('email')) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" step="0.01" min="0" value="{{ old('tax_rate', setting('tax_rate', 0)) }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Address</label>
                        <input type="text" name="address" value="{{ old('address', setting('address')) }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Invoice Footer</label>
                        <textarea name="invoice_footer" rows="2" class="input">{{ old('invoice_footer', setting('invoice_footer')) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-800">Device Types</h2>
                <form method="POST" action="{{ route('settings.device-types.store') }}" class="mb-4 flex gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="e.g. Smartphone" required class="input">
                    <button type="submit" class="btn-primary">Add</button>
                </form>
                <div class="space-y-3">
                    @forelse ($deviceTypes as $deviceType)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <p class="text-sm font-semibold text-slate-800">{{ $deviceType->name }}</p>
                            @if ($deviceType->brands->isNotEmpty())
                                <p class="mt-1 text-xs text-slate-500">Brands: {{ $deviceType->brands->pluck('name')->implode(', ') }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No device types yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-800">Brands</h2>
                <form method="POST" action="{{ route('settings.brands.store') }}" class="space-y-3">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="name" placeholder="e.g. Samsung" required class="input">
                        <select name="device_type_id" class="input">
                            <option value="">All types</option>
                            @foreach ($deviceTypes as $deviceType)
                                <option value="{{ $deviceType->id }}">{{ $deviceType->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary">Add</button>
                    </div>
                </form>
                @php $allBrands = $deviceTypes->flatMap(fn ($t) => $t->brands)->unique('id'); @endphp
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse ($allBrands as $brand)
                        <span class="badge-slate">{{ $brand->name }}@if ($brand->deviceType) ({{ $brand->deviceType->name }})@endif</span>
                    @empty
                        <p class="text-sm text-slate-400">No brands yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Part Categories</h2>
            <form method="POST" action="{{ route('settings.categories.store') }}" class="mb-4 flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="e.g. Screen" required class="input">
                <button type="submit" class="btn-primary">Add</button>
            </form>
            <div class="flex flex-wrap gap-2">
                @forelse ($partCategories as $category)
                    <span class="badge-indigo">{{ $category->name }}</span>
                @empty
                    <p class="text-sm text-slate-400">No categories yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Repair Services</h2>
            <form method="POST" action="{{ route('settings.repair-services.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="e.g. Screen Replacement" required class="input">
                <div class="grid gap-2 sm:grid-cols-3">
                    <select name="device_type_id" class="input">
                        <option value="">All types</option>
                        @foreach ($deviceTypes as $deviceType)
                            <option value="{{ $deviceType->id }}">{{ $deviceType->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="estimated_cost" step="0.01" min="0" placeholder="Est. cost" class="input">
                    <input type="number" name="estimated_time_hours" min="0" placeholder="Est. hours" class="input">
                </div>
                <button type="submit" class="btn-primary">Add Service</button>
            </form>
            <div class="mt-4 space-y-2">
                @forelse ($repairServices as $service)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <span class="font-medium text-slate-800">{{ $service->name }}</span>
                        <span class="text-xs text-slate-500">
                            {{ $service->deviceType?->name ?: 'All types' }}
                            @if ($service->estimated_cost)
                                · {{ format_money($service->estimated_cost) }}
                            @endif
                            @if ($service->estimated_time_hours)
                                · {{ $service->estimated_time_hours }}h
                            @endif
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No repair services yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
