<?php

namespace App\Http\Requests\V2;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Customer Details tab.
 */
class CustomerDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'is_active' => ['boolean'],
            'require_dimensions' => ['boolean'],
            'network_customer' => ['boolean'],
            'quote_required' => ['boolean'],
            'location_sharing' => ['required', Rule::in(array_keys(Customer::LOCATION_SHARING))],
            'currency' => ['required', Rule::in(Customer::CURRENCIES)],
            'customer_type' => ['required', Rule::in(array_keys(Customer::TYPES))],
            'default_billing_option' => ['required', Rule::in(array_keys(Customer::BILLING_OPTIONS))],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.max' => 'The logo must be 2 MB or smaller.',
            'logo.mimes' => 'Upload the logo as a PNG, JPG or WebP image.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'require_dimensions' => $this->boolean('require_dimensions'),
            'network_customer' => $this->boolean('network_customer'),
            'quote_required' => $this->boolean('quote_required'),
            'remove_logo' => $this->boolean('remove_logo'),
            'credit_limit' => $this->filled('credit_limit') ? str_replace(',', '', (string) $this->input('credit_limit')) : null,
        ]);
    }
}
