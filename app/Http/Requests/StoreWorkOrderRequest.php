<?php

namespace App\Http\Requests;

use App\Enums\WorkOrderPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'device_id' => ['required', 'exists:customer_devices,id'],
            'technician_id' => ['nullable', 'exists:technicians,id'],
            'repair_service_id' => ['nullable', 'exists:repair_services,id'],
            'problem_description' => ['required', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'priority' => ['required', Rule::enum(WorkOrderPriority::class)],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estimated_date' => ['nullable', 'date'],
        ];
    }
}
