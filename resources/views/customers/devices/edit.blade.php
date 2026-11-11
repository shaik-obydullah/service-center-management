@extends('layouts.app')

@section('title', 'Edit Device')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Edit Device</h2>
                <a href="{{ route('customers.show', $customer) }}" class="btn-secondary btn-sm">← Back</a>
            </div>
            <form method="POST" action="{{ route('customers.devices.update', [$customer, $device]) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Device Type</label>
                        <select name="device_type_id" class="input">
                            <option value="">Select type...</option>
                            @foreach ($deviceTypes as $id => $name)
                                <option value="{{ $id }}" @selected(old('device_type_id', $device->device_type_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Type / Category *</label>
                        <input type="text" name="type" value="{{ old('type', $device->type) }}" required class="input">
                    </div>
                    <div>
                        <label class="label">Brand *</label>
                        <input type="text" name="brand" value="{{ old('brand', $device->brand) }}" required class="input">
                    </div>
                    <div>
                        <label class="label">Model *</label>
                        <input type="text" name="model" value="{{ old('model', $device->model) }}" required class="input">
                    </div>
                    <div>
                        <label class="label">Serial Number</label>
                        <input type="text" name="serial_number" value="{{ old('serial_number', $device->serial_number) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Color</label>
                        <input type="text" name="color" value="{{ old('color', $device->color) }}" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Notes</label>
                        <textarea name="notes" rows="3" class="input">{{ old('notes', $device->notes) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Update Device</button>
                </div>
            </form>
        </div>
    </div>
@endsection
