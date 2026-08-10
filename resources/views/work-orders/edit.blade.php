@extends('layouts.app')

@section('title', 'Edit Work Order')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Edit {{ $workOrder->order_number }}</h2>
            <form method="POST" action="{{ route('work-orders.update', $workOrder) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Technician</label>
                        <select name="technician_id" class="input">
                            <option value="">Unassigned</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected(old('technician_id', $workOrder->technician_id) == $technician->id)>{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Repair Service</label>
                        <select name="repair_service_id" class="input">
                            <option value="">None</option>
                            @foreach ($repairServices as $service)
                                <option value="{{ $service->id }}" @selected(old('repair_service_id', $workOrder->repair_service_id) == $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Priority *</label>
                        <select name="priority" required class="input">
                            @foreach (\App\Enums\WorkOrderPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority', $workOrder->priority) == $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Estimated Date</label>
                        <input type="date" name="estimated_date" value="{{ old('estimated_date', $workOrder->estimated_date?->format('Y-m-d')) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Estimated Cost *</label>
                        <input type="number" name="estimated_cost" step="0.01" min="0" value="{{ old('estimated_cost', $workOrder->estimated_cost) }}" required class="input">
                        @error('estimated_cost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Actual Cost</label>
                        <input type="number" name="actual_cost" step="0.01" min="0" value="{{ old('actual_cost', $workOrder->actual_cost) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Discount (%)</label>
                        <input type="number" name="discount" step="0.01" min="0" max="100" value="{{ old('discount', $workOrder->discount) }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Problem Description *</label>
                        <textarea name="problem_description" rows="3" required class="input">{{ old('problem_description', $workOrder->problem_description) }}</textarea>
                        @error('problem_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Diagnosis</label>
                        <textarea name="diagnosis" rows="2" class="input">{{ old('diagnosis', $workOrder->diagnosis) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('work-orders.show', $workOrder) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
