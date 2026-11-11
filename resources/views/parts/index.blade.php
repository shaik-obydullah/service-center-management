@extends('layouts.app')

@section('title', 'Parts Inventory')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex flex-wrap gap-2" method="GET" action="{{ route('parts.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, code or brand..."
                   class="input w-64">
            <select name="category" class="input w-48" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('parts.index', ['filter' => 'low']) }}"
               class="btn-secondary {{ request('filter') === 'low' ? 'ring-2 ring-amber-300' : '' }}">
                Low Stock @if ($lowStockCount > 0)<span class="badge-red ml-1">{{ $lowStockCount }}</span>@endif
            </a>
            @if (request()->hasAny(['search', 'category', 'filter']))
                <a href="{{ route('parts.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
        <a href="{{ route('parts.create') }}" class="btn-primary">+ Add Part</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Stock</th>
                        <th>Cost</th>
                        <th>Selling</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parts as $part)
                        <tr class="hover:bg-slate-50">
                            <td class="font-medium text-indigo-600"><a href="{{ route('parts.show', $part) }}">{{ $part->code }}</a></td>
                            <td class="font-medium">{{ $part->name }}</td>
                            <td class="text-slate-500">{{ $part->partCategory?->name ?: '-' }}</td>
                            <td>{{ $part->brand ?: '-' }}</td>
                            <td>
                                <span class="inline-flex items-center gap-2">
                                    <span class="font-medium">{{ $part->quantity }}</span>
                                    @if ($part->is_low_stock)
                                        <span class="badge-amber">Low</span>
                                    @endif
                                    @if ($part->quantity <= 0)
                                        <span class="badge-red">Out</span>
                                    @endif
                                </span>
                            </td>
                            <td>{{ format_money($part->cost_price) }}</td>
                            <td>{{ format_money($part->selling_price) }}</td>
                            <td>
                                <span class="badge-{{ $part->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($part->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">No parts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $parts->links() }}
    </div>
@endsection
