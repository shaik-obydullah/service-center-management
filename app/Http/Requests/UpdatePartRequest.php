<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'part_category_id' => ['nullable', 'exists:part_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', 'unique:parts,code,' . $this->route('part')?->id],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'numeric', 'min:0'],
            'minimum_stock' => ['sometimes', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
