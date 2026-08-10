@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $types = [
            'revenue' => 'Revenue',
            'technicians' => 'Technician Performance',
            'popular-repairs' => 'Popular Repairs',
            'inventory' => 'Inventory',
        ];
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($types as $value => $label)
                <a href="{{ route('reports.index', array_merge(['type' => $value], array_filter(['from' => $from, 'to' => $to]))) }}"
                   class="btn {{ $type === $value ? 'btn-primary' : 'btn-secondary' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <form class="flex items-center gap-2" method="GET" action="{{ route('reports.index') }}">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="date" name="from" value="{{ $from }}" class="input w-40">
                <input type="date" name="to" value="{{ $to }}" class="input w-40">
                <button type="submit" class="btn-secondary">Apply</button>
            </form>
            <a href="{{ route('exports.report', array_merge(['type' => $type, 'format' => 'xlsx'], array_filter(['from' => $from, 'to' => $to]))) }}" class="btn-secondary">Export Excel</a>
            <a href="{{ route('exports.report', array_merge(['type' => $type, 'format' => 'csv'], array_filter(['from' => $from, 'to' => $to]))) }}" class="btn-secondary">Export CSV</a>
        </div>
    </div>

    @if ($type === 'revenue')
        @php
            $fromLabel = $data['from']->format('M j, Y');
            $toLabel = $data['to']->format('M j, Y');
        @endphp
        <div class="mb-4 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="card p-5">
                <p class="text-sm text-slate-500">Period</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $fromLabel }} — {{ $toLabel }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Invoices</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $data['invoice_count'] }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Total Billed</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ format_money($data['total_billed']) }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Collected</p>
                <p class="mt-1 text-2xl font-bold text-green-600">{{ format_money($data['total_collected']) }}</p>
            </div>
        </div>
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Daily Revenue</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Date</th>
                            <th>Invoices</th>
                            <th>Billed</th>
                            <th>Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['daily'] as $day)
                            <tr>
                                <td>{{ \Carbon\CarbonImmutable::parse($day->date)->format('M j, Y') }}</td>
                                <td>{{ $day->invoice_count ?? 0 }}</td>
                                <td>{{ format_money($day->total) }}</td>
                                <td class="text-slate-500">{{ format_money($data['total_tax'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-slate-400">No invoice data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($type === 'technicians')
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Technician Performance</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Technician</th>
                            <th>Employee ID</th>
                            <th>Total Orders</th>
                            <th>Completed</th>
                            <th>Revenue</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $tech)
                            <tr>
                                <td class="font-medium">{{ $tech->name }}</td>
                                <td class="font-mono text-xs text-slate-500">{{ $tech->employee_id }}</td>
                                <td>{{ $tech->total_orders }}</td>
                                <td class="text-green-600">{{ $tech->completed_orders }}</td>
                                <td>{{ format_money($tech->revenue) }}</td>
                                <td>{{ number_format($tech->total_hours, 1) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($type === 'popular-repairs')
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Popular Repairs</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Repair Service</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $repair)
                            <tr>
                                <td class="font-medium">{{ $repair->name }}</td>
                                <td>{{ $repair->total_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-6 text-center text-slate-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($type === 'inventory')
        <div class="mb-4 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="card p-5">
                <p class="text-sm text-slate-500">Total Parts</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $data['part_count'] }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Stock Value</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ format_money($data['stock_value']) }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Low Stock</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $data['low_stock_count'] }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500">Out of Stock</p>
                <p class="mt-1 text-2xl font-bold text-red-600">{{ $data['out_of_stock_count'] }}</p>
            </div>
        </div>
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800">Inventory by Category</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th>Category</th>
                            <th>Parts</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['by_category'] as $category)
                            <tr>
                                <td class="font-medium">{{ $category->name }}</td>
                                <td>{{ $category->part_count }}</td>
                                <td>{{ $category->total_quantity }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-slate-400">No parts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
