@extends('layouts.app')

@section('title', 'Add Technician')

@section('content')
    <div class="w-full">
        <div class="card p-6">
            <h2 class="mb-6 text-lg font-semibold text-slate-800">Technician Information</h2>
            <form method="POST" action="{{ route('technicians.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Employee ID *</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" required class="input" placeholder="e.g. TEC-001">
                        @error('employee_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required class="input">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Hourly Rate</label>
                        <input type="number" name="hourly_rate" step="0.01" min="0" value="{{ old('hourly_rate') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="active" @selected(old('status', 'active') == 'active')>Active</option>
                            <option value="inactive" @selected(old('status') == 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">User Account (optional)</label>
                        <select name="user_id" class="input">
                            <option value="">No user account</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Skills</label>
                        <div x-data="{ skills: @json(old('skills_json', [])) }">
                            <div class="space-y-2">
                                <template x-for="(skill, index) in skills" :key="index">
                                    <div class="flex gap-2">
                                        <input type="text" name="skills_json[]" x-model="skills[index]" class="input" placeholder="Skill">
                                        <button type="button" class="btn-danger btn-sm" @click="skills.splice(index, 1)">Remove</button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" class="btn-secondary btn-sm mt-2" @click="skills.push('')">+ Add skill</button>
                        </div>
                        @error('skills_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('technicians.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Technician</button>
                </div>
            </form>
        </div>
    </div>
@endsection
