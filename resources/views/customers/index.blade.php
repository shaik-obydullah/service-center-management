@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2" method="GET" action="{{ route('customers.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone or email..."
                   class="input w-64">
            <button type="submit" class="btn-secondary">Search</button>
        </form>
        <a href="{{ route('customers.create') }}" class="btn-primary">+ Add Customer</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Devices</th>
                        <th>Orders</th>
                        <th>Loyalty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td><a href="{{ route('customers.show', $customer) }}" class="font-medium text-indigo-600 hover:underline">{{ $customer->name }}</a></td>
                            <td>{{ $customer->phone }}</td>
                            <td class="text-slate-500">{{ $customer->email ?: '-' }}</td>
                            <td>{{ $customer->city ?: '-' }}</td>
                            <td>{{ $customer->devices_count }}</td>
                            <td>{{ $customer->work_orders_count }}</td>
                            <td>
                                @if ($customer->loyalty_member)
                                    <span class="badge-green">Member</span>
                                @else
                                    <span class="badge-slate">Regular</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $customers->links() }}
        </div>
    </div>
@endsection
