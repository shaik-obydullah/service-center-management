@extends('layouts.app')

@section('title', 'Technicians')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2" method="GET" action="{{ route('technicians.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, ID or phone..."
                   class="input w-64">
            <button type="submit" class="btn-secondary">Search</button>
            @if (request('search'))
                <a href="{{ route('technicians.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
        <a href="{{ route('technicians.create') }}" class="btn-primary">+ Add Technician</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Skills</th>
                        <th>Work Orders</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($technicians as $technician)
                        <tr class="hover:bg-slate-50">
                            <td class="font-mono text-xs text-slate-500">{{ $technician->employee_id }}</td>
                            <td><a href="{{ route('technicians.show', $technician) }}" class="font-medium text-indigo-600 hover:underline">{{ $technician->name }}</a></td>
                            <td>{{ $technician->phone }}</td>
                            <td class="text-slate-500">{{ $technician->email ?: '-' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($technician->skills_json ?? [] as $skill)
                                        <span class="badge-slate">{{ $skill }}</span>
                                    @empty
                                        <span class="text-slate-400">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>{{ $technician->work_orders_count }}</td>
                            <td>
                                <span class="badge-{{ $technician->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($technician->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No technicians found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $technicians->links() }}
    </div>
@endsection
