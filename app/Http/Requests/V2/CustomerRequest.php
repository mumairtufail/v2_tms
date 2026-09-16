<?php

namespace App\Http\Requests\V2;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create customer modal.
 */
class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = app('current.company')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'short_code' => [
                'nullable',
                'string',
                'regex:/^[A-Z0-9]{3,4}$/',
                Rule::unique('customers', 'short_code')
                    ->where(fn ($q) => $q->where('company_id', $companyId)->where('is_deleted', false)),
            ],
            'address_1' => ['required', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:10'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'currency' => ['required', Rule::in(Customer::CURRENCIES)],
            'customer_type' => ['nullable', Rule::in(array_keys(Customer::TYPES))],
            'quote_required' => ['boolean'],
            'default_billing_option' => ['required', Rule::in(array_keys(Customer::BILLING_OPTIONS))],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'organization name',
            'address_1' => 'address 1',
            'state' => 'province / state',
            'postal_code' => 'postal code',
        ];
    }

    public function messages(): array
    {
        return [
            'short_code.regex' => 'Short codes are 3 or 4 letters and numbers, like 7ML4.',
            'short_code.unique' => 'Another customer already uses this short code.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quote_required' => $this->boolean('quote_required'),
            'short_code' => $this->filled('short_code') ? strtoupper(trim((string) $this->input('short_code'))) : null,
            'country' => $this->filled('country') ? strtoupper(trim((string) $this->input('country'))) : null,
        ]);
    }
}
