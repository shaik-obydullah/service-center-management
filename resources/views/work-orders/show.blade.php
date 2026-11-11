@extends('layouts.app')

@section('title', $workOrder->order_number)

@section('content')
    @php
        $status = \App\Enums\WorkOrderStatus::tryFrom($workOrder->status);
        $priority = \App\Enums\WorkOrderPriority::tryFrom($workOrder->priority);
        $nextStatuses = \App\Enums\WorkOrderStatus::workflow()[$workOrder->status] ?? [];
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('work-orders.index') }}" class="btn-secondary btn-sm">← Back</a>
            <h2 class="text-xl font-semibold text-slate-800">{{ $workOrder->order_number }}</h2>
            <span class="badge-{{ $status?->color() ?? 'slate' }}">{{ $status?->label() ?? $workOrder->status }}</span>
            <span class="badge-{{ $priority?->color() ?? 'slate' }}">{{ $priority?->label() ?? $workOrder->priority }}</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn-secondary btn-sm">Edit</a>
            @if ($workOrder->invoice)
                <a href="{{ route('invoices.show', $workOrder->invoice) }}" class="btn-secondary btn-sm">View Invoice</a>
            @elseif ($status && in_array($workOrder->status, ['ready', 'in_repair', 'completed']))
                <a href="{{ route('invoices.create', $workOrder) }}" class="btn-primary btn-sm">Generate Invoice</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Customer & Device</h3>
                <div class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-slate-500">Customer</p>
                        <p class="font-medium"><a href="{{ route('customers.show', $workOrder->customer) }}" class="text-indigo-600 hover:underline">{{ $workOrder->customer->name }}</a></p>
                        <p class="text-slate-500">{{ $workOrder->customer->phone }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Device</p>
                        @if ($workOrder->device)
                            <p class="font-medium">{{ $workOrder->device->brand }} {{ $workOrder->device->model }}</p>
                            <p class="text-slate-500">{{ $workOrder->device->type }} · SN: {{ $workOrder->device->serial_number ?: '-' }}</p>
                        @else
                            <p class="text-slate-500">-</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-slate-500">Repair Service</p>
                        <p class="font-medium">{{ $workOrder->repairService?->name ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Technician</p>
                        <p class="font-medium">{{ $workOrder->technician?->name ?: 'Unassigned' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Estimated Cost</p>
                        <p class="font-medium">{{ format_money($workOrder->estimated_cost) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Discount</p>
                        <p class="font-medium">{{ $workOrder->discount ? $workOrder->discount . '%' : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Problem & Diagnosis</h3>
                <p class="text-sm text-slate-600">{{ $workOrder->problem_description }}</p>
                @if ($workOrder->diagnosis)
                    <h4 class="mt-4 text-sm font-medium text-slate-700">Diagnosis</h4>
                    <p class="mt-1 text-sm text-slate-600">{{ $workOrder->diagnosis }}</p>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-800">Parts Used</h3>
                    @if (!$workOrder->invoice)
                        <button type="button" class="btn-secondary btn-sm" x-data
                                @click="$dispatch('open-parts-modal')">+ Add Parts</button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th>Part</th>
                                <th>Code</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($workOrder->partUsages as $usage)
                                <tr>
                                    <td class="font-medium">{{ $usage->part->name }}</td>
                                    <td class="text-slate-500">{{ $usage->part->code }}</td>
                                    <td>{{ $usage->quantity }}</td>
                                    <td>{{ format_money($usage->unit_price) }}</td>
                                    <td>{{ format_money($usage->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-slate-400">No parts used yet.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($workOrder->partUsages->isNotEmpty())
                            <tfoot>
                                <tr class="bg-slate-50 font-semibold">
                                    <td colspan="4" class="text-right">Parts Total</td>
                                    <td>{{ format_money($workOrder->parts_total) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Notes</h3>
                <form method="POST" action="{{ route('work-orders.notes', $workOrder) }}" class="mb-4 flex gap-2">
                    @csrf
                    <input type="text" name="note" placeholder="Add a note..." required class="input">
                    <button type="submit" class="btn-secondary">Add</button>
                </form>
                <ul class="space-y-3">
                    @forelse ($workOrder->notes as $note)
                        <li class="rounded-lg bg-slate-50 px-4 py-3 text-sm">
                            <p class="text-slate-700">{{ $note->note }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $note->user?->name ?? 'System' }} · {{ $note->created_at->diffForHumans() }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">No notes yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Update Status</h3>
                @if (count($nextStatuses) > 0)
                    <form method="POST" action="{{ route('work-orders.status', $workOrder) }}" class="space-y-3">
                        @csrf
                        <select name="status" class="input">
                            @foreach ($nextStatuses as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="notes" placeholder="Notes (optional)" class="input">
                        <button type="submit" class="btn-primary w-full justify-center">Update Status</button>
                    </form>
                @else
                    <p class="text-sm text-slate-400">No further status transitions available.</p>
                @endif
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Assign Technician</h3>
                <form method="POST" action="{{ route('work-orders.assign', $workOrder) }}" class="space-y-3">
                    @csrf
                    <select name="technician_id" class="input">
                        @foreach (\App\Models\Technician::where('status', 'active')->orderBy('name')->get() as $tech)
                            <option value="{{ $tech->id }}" @selected($workOrder->technician_id == $tech->id)>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-secondary w-full justify-center">Assign</button>
                </form>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-slate-800">Status History</h3>
                <ol class="space-y-3">
                    @forelse ($workOrder->statusHistory as $history)
                        <li class="border-l-2 border-slate-200 pl-3 text-sm">
                            <p class="font-medium">
                                @php $hStatus = \App\Enums\WorkOrderStatus::tryFrom($history->status); @endphp
                                <span class="badge-{{ $hStatus?->color() ?? 'slate' }}">{{ $hStatus?->label() ?? $history->status }}</span>
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $history->user?->name ?? 'System' }} · {{ $history->created_at->format('M j, Y g:i A') }}
                            </p>
                            @if ($history->notes)
                                <p class="text-xs text-slate-500">{{ $history->notes }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">No history.</li>
                    @endforelse
                </ol>
            </div>

            <div class="card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Warranties</h3>
                    <a href="{{ route('warranties.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>
                @if ($workOrder->warranties->isEmpty())
                    <p class="mb-3 text-sm text-slate-400">No warranties for this work order.</p>
                @else
                    <ul class="mb-4 space-y-2">
                        @foreach ($workOrder->warranties as $warranty)
                            <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                <div>
                                    <a href="{{ route('warranties.show', $warranty) }}" class="font-medium text-indigo-600 hover:underline">
                                        {{ $warranty->part?->name ?: 'Service warranty' }}
                                    </a>
                                    <p class="text-xs text-slate-400">Until {{ $warranty->end_date->format('M j, Y') }}</p>
                                </div>
                                <span class="badge-{{ $warranty->status_badge }}">{{ $warranty->status_label }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <form method="POST" action="{{ route('warranties.store', $workOrder) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label">Part (optional)</label>
                        <select name="part_id" class="input">
                            <option value="">Service warranty (no part)</option>
                            @foreach ($workOrder->partUsages as $usage)
                                <option value="{{ $usage->part_id }}">{{ $usage->part->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="label">Duration (days)</label>
                            <input type="number" name="duration_days" min="1" value="{{ setting('default_warranty_days', 30) }}" required class="input">
                        </div>
                        <div>
                            <label class="label">Start Date</label>
                            <input type="date" name="start_date" value="{{ now()->toDateString() }}" required class="input">
                        </div>
                    </div>
                    <input type="text" name="notes" placeholder="Notes (optional)" class="input">
                    <button type="submit" class="btn-secondary w-full justify-center">+ Add Warranty</button>
                </form>
            </div>
        </div>
    </div>

    @if (!$workOrder->invoice)
        <div x-data="{ open: false }" @open-parts-modal.window="open = true"
             x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl" @click.outside="open = false">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Add Parts to Work Order</h3>
                <form method="POST" action="{{ route('work-orders.parts', $workOrder) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label">Parts</label>
                        <div class="space-y-2" x-data="{ rows: [{ part_id: '', quantity: 1 }] }" x-init="$watch('rows', () => {})">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="flex gap-2">
                                    <select :name="`items[${index}][part_id]`" x-model="row.part_id" class="input" required>
                                        <option value="">Select part...</option>
                                        @foreach (\App\Models\Part::where('status', 'active')->orderBy('name')->get() as $part)
                                            <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->quantity }} in stock)</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" :name="`items[${index}][quantity]`" x-model="row.quantity" class="input w-24" placeholder="Qty" required>
                                    <button type="button" class="btn-danger btn-sm" x-show="rows.length > 1"
                                            @click="rows.splice(index, 1)">Remove</button>
                                </div>
                            </template>
                            <button type="button" class="btn-secondary btn-sm"
                                    @click="rows.push({ part_id: '', quantity: 1 })">+ Add another part</button>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="btn-secondary" @click="open = false">Cancel</button>
                        <button type="submit" class="btn-primary">Use Parts</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
