<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
        $company = $this->route('company');
        $customer = $this->route('customer');

        $portalEnabled = $this->boolean('portal');

        // Password is mandatory when portal access is enabled, unless the
        // customer already has one (blank on update keeps the current password).
        $passwordRequired = $portalEnabled && (!$isUpdate || !$customer?->password);

        return [
            'name' => ['required', 'string', 'max:255'],
            'short_code' => [
                'nullable',
                'string',
                'max:3',
                'regex:/^[A-Z0-9]{1,3}$/',
                Rule::unique('customers', 'short_code')
                    ->where(fn ($q) => $q->where('company_id', $company?->id)->where('is_deleted', false))
                    ->ignore($customer?->id),
            ],
            'customer_email' => [
                Rule::requiredIf($portalEnabled),
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'customer_email')
                    ->where(fn ($q) => $q->where('company_id', $company?->id)->where('is_deleted', false))
                    ->ignore($customer?->id),
            ],
            'password' => [
                Rule::requiredIf($passwordRequired),
                'nullable',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
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
            'currency' => ['nullable', 'string', Rule::in(['USD', 'CAD'])],
        ];
    }

    public function messages(): array
    {
        return [
            'short_code.regex' => 'The short code may only contain letters and numbers (up to 3 characters).',
            'short_code.unique' => 'This short code is already used by another customer.',
            'customer_email.required' => 'An email address is required when portal access is enabled.',
            'customer_email.unique' => 'Another customer is already using this email address.',
            'password.required' => 'A password is required when portal access is enabled.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'portal' => $this->boolean('portal'),
            'quote_required' => $this->boolean('quote_required'),
            'short_code' => $this->filled('short_code')
                ? strtoupper(trim((string) $this->input('short_code')))
                : null,
        ]);
    }
}
