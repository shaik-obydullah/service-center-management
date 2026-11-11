<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technician_id' => ['nullable', 'exists:technicians,id'],
            'problem_description' => ['sometimes', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'estimated_cost' => ['sometimes', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estimated_date' => ['nullable', 'date'],
        ];
    }
}
