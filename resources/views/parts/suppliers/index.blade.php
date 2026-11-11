@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2" method="GET" action="{{ route('suppliers.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or contact..."
                   class="input w-64">
            <button type="submit" class="btn-secondary">Search</button>
            @if (request('search'))
                <a href="{{ route('suppliers.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
        <a href="{{ route('suppliers.create') }}" class="btn-primary">+ Add Supplier</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Parts</th>
                        <th>Purchase Orders</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-slate-50">
                            <td><a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-indigo-600 hover:underline">{{ $supplier->name }}</a></td>
                            <td>{{ $supplier->contact_person ?: '-' }}</td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td class="text-slate-500">{{ $supplier->email ?: '-' }}</td>
                            <td>{{ $supplier->parts_count }}</td>
                            <td>{{ $supplier->purchase_orders_count }}</td>
                            <td>
                                <span class="badge-{{ $supplier->status ? 'green' : 'slate' }}">{{ $supplier->status ? 'Active' : 'Inactive' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $suppliers->links() }}
    </div>
@endsection
