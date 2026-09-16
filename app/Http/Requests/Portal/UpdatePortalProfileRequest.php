<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * The signed-in person's own name and sign-in email.
 */
class UpdatePortalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('customer')->check();
    }

    public function rules(): array
    {
        $contact = Auth::guard('customer')->user();
        $company = app('current.company');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customer_contacts', 'email')
                    ->where(fn ($q) => $q->where('company_id', $company->id)->where('portal_access', true)->whereNull('deleted_at'))
                    ->ignore($contact->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Someone else already signs in with this email.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }
}
