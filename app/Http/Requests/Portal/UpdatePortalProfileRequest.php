<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePortalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('customer')->check();
    }

    public function rules(): array
    {
        $customer = Auth::guard('customer')->user();
        $company = app('current.company');

        return [
            'name' => ['required', 'string', 'max:255'],
            'customer_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'customer_email')
                    ->where('company_id', $company->id)
                    ->ignore($customer->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency') && is_string($this->currency)) {
            $this->merge([
                'currency' => strtoupper($this->currency),
            ]);
        }
    }
}
