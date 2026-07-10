<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => ['required', 'string', 'max:255'],
            'short_code' => ['nullable', 'string', 'max:3'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'password' => [
                $isUpdate ? 'nullable' : 'nullable',
                'required_if:portal,1,true',
                'string',
                Password::min(8),
                'confirmed',
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'portal' => ['boolean'],
            'quote_required' => ['boolean'],
            'customer_type' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:3'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'portal' => $this->boolean('portal'),
            'quote_required' => $this->boolean('quote_required'),
        ]);
    }
}
