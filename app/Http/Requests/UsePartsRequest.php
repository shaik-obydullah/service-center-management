<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsePartsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.part_id' => ['required', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
