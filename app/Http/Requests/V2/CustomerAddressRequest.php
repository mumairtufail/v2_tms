<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Addresses tab: a plain postal address. A customer can have as many as needed.
 */
class CustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_1' => ['required', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:10'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'is_billing' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'address_1' => 'address 1',
            'address_2' => 'address 2',
            'state' => 'province / state',
            'postal_code' => 'postal code',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_billing' => $this->boolean('is_billing'),
            'country' => $this->filled('country') ? strtoupper(trim((string) $this->input('country'))) : null,
            'state' => $this->filled('state') ? trim((string) $this->input('state')) : null,
        ]);
    }
}
