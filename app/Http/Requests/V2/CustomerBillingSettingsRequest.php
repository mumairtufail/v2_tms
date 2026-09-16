<?php

namespace App\Http\Requests\V2;

use App\Models\CustomerBillingSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerBillingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_terms' => ['nullable', Rule::in(array_keys(CustomerBillingSetting::TERMS))],
            'taxable' => ['boolean'],
            'recipients' => ['array', 'max:' . CustomerBillingSetting::MAX_RECIPIENTS],
            'recipients.*' => ['email', 'max:255', 'distinct'],
            'attach_proof_of_pickup' => ['boolean'],
            'attach_proof_of_delivery' => ['boolean'],
            'attach_commercial_invoice' => ['boolean'],
            'combine_documents' => ['boolean'],
            'bulk_send_method' => ['required', Rule::in(array_keys(CustomerBillingSetting::SEND_METHODS))],
        ];
    }

    public function attributes(): array
    {
        return ['recipients.*' => 'recipient email'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipients' => collect((array) $this->input('recipients', []))
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->filter()
                ->values()
                ->all(),
            'taxable' => $this->boolean('taxable'),
            'attach_proof_of_pickup' => $this->boolean('attach_proof_of_pickup'),
            'attach_proof_of_delivery' => $this->boolean('attach_proof_of_delivery'),
            'attach_commercial_invoice' => $this->boolean('attach_commercial_invoice'),
            'combine_documents' => $this->boolean('combine_documents'),
        ]);
    }
}
