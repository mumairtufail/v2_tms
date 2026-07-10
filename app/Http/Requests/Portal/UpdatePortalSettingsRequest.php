<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePortalSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('customer')->check();
    }

    public function rules(): array
    {
        return [
            'location_sharing' => [
                'required',
                Rule::in(['Do not share', 'approximate', 'exact live location']),
            ],
            'default_billing_option' => [
                'required',
                Rule::in(['third_party', 'consignee', 'shipper']),
            ],
            'network_customer' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'network_customer' => $this->boolean('network_customer'),
        ]);
    }
}
