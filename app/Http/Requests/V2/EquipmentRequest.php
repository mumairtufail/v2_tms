<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class EquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'sub_type' => ['nullable', 'string', 'max:50'],
            'desc' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'last_seen' => ['nullable', 'date'],
            'last_location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
