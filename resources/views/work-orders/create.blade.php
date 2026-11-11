@extends('layouts.app')

@section('title', 'New Work Order')

@section('content')
    <div class="w-full">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Work Order Details</h2>
            <form method="POST" action="{{ route('work-orders.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Customer *</label>
                        <select name="customer_id" id="customer-select" required class="input">
                            <option value="">Select customer...</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomer?->id) == $customer->id)>{{ $customer->name }} ({{ $customer->phone }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Device *</label>
                        <select name="device_id" id="device-select" required class="input" disabled>
                            <option value="">Select customer first...</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}" @selected(old('device_id') == $device->id)>{{ $device->brand }} {{ $device->model }} ({{ $device->serial_number }})</option>
                            @endforeach
                        </select>
                        @error('device_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Technician</label>
                        <select name="technician_id" class="input">
                            <option value="">Unassigned</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected(old('technician_id') == $technician->id)>{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Repair Service</label>
                        <select name="repair_service_id" class="input">
                            <option value="">None</option>
                            @foreach ($repairServices as $service)
                                <option value="{{ $service->id }}" @selected(old('repair_service_id') == $service->id)>
                                    {{ $service->name }}@if ($service->estimated_cost) ({{ format_money($service->estimated_cost) }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Priority *</label>
                        <select name="priority" required class="input">
                            @foreach (\App\Enums\WorkOrderPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority', 'medium') == $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Estimated Date</label>
                        <input type="date" name="estimated_date" value="{{ old('estimated_date') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Estimated Cost *</label>
                        <input type="number" name="estimated_cost" step="0.01" min="0" value="{{ old('estimated_cost', 0) }}" required class="input">
                        @error('estimated_cost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Discount (%)</label>
                        <input type="number" name="discount" step="0.01" min="0" max="100" value="{{ old('discount', 0) }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Problem Description *</label>
                        <textarea name="problem_description" rows="3" required class="input">{{ old('problem_description') }}</textarea>
                        @error('problem_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Initial Diagnosis</label>
                        <textarea name="diagnosis" rows="2" class="input">{{ old('diagnosis') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('work-orders.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Work Order</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('customer-select').addEventListener('change', async function () {
            const deviceSelect = document.getElementById('device-select');
            deviceSelect.disabled = true;
            deviceSelect.innerHTML = '<option value="">Loading...</option>';

            if (! this.value) {
                deviceSelect.innerHTML = '<option value="">Select customer first...</option>';
                return;
            }

            const res = await fetch('{{ route("work-orders.devices-json") }}?customer_id=' + this.value, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const devices = await res.json();

            deviceSelect.innerHTML = '<option value="">Select device...</option>' + devices.map(d =>
                `<option value="${d.id}">${d.label}</option>`
            ).join('');
            deviceSelect.disabled = false;
        });
    </script>
@endsection
