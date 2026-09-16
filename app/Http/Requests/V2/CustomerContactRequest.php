<?php

namespace App\Http\Requests\V2;

use App\Models\CustomerContact;
use App\Models\CustomerContactPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * People tab: create / edit a person at a customer.
 */
class CustomerContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = app('current.company')?->id;
        $contact = $this->route('contact');
        $contactId = $contact instanceof CustomerContact ? $contact->id : null;
        $portalAccess = $this->boolean('portal_access');

        // A password is needed to turn portal access on, unless this person already has one.
        $needsPassword = $portalAccess && !($contact instanceof CustomerContact && filled($contact->password));

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'email' => [
                Rule::requiredIf($portalAccess || $this->boolean('send_invoices') || $this->boolean('cc_on_invoices')),
                'nullable',
                'email',
                'max:255',
                Rule::when($portalAccess, [
                    Rule::unique('customer_contacts', 'email')
                        ->where(fn ($q) => $q->where('company_id', $companyId)->where('portal_access', true)->whereNull('deleted_at'))
                        ->ignore($contactId),
                ]),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'phones' => ['array', 'max:5'],
            'phones.*.type' => ['required', Rule::in(array_keys(CustomerContactPhone::TYPES))],
            'phones.*.number' => ['required', 'string', 'max:50'],
            'phones.*.ext' => ['nullable', 'string', 'max:10'],
            'send_invoices' => ['boolean'],
            'portal_access' => ['boolean'],
            'cc_on_invoices' => ['boolean'],
            'email_notifications' => ['array'],
            'password' => [
                Rule::requiredIf($needsPassword),
                'nullable',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'phones.*.number' => 'phone number',
            'phones.*.type' => 'phone type',
            'phones.*.ext' => 'extension',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'An email is needed for portal access or invoice emails.',
            'email.unique' => 'Another person with portal access already signs in with this email.',
            'password.required' => 'Set a portal password to turn on portal access.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $phones = collect((array) $this->input('phones', []))
            ->filter(fn ($phone) => is_array($phone) && trim((string) ($phone['number'] ?? '')) !== '')
            ->map(fn ($phone) => [
                'type' => $phone['type'] ?? 'office',
                'number' => trim((string) $phone['number']),
                'ext' => trim((string) ($phone['ext'] ?? '')) ?: null,
            ])
            ->values()
            ->all();

        $this->merge([
            'phones' => $phones,
            'send_invoices' => $this->boolean('send_invoices'),
            'portal_access' => $this->boolean('portal_access'),
            'cc_on_invoices' => $this->boolean('cc_on_invoices'),
            'email_notifications' => (array) $this->input('email_notifications', []),
            'email' => $this->filled('email') ? strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }
}
