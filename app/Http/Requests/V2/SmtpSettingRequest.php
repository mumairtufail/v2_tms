<?php

namespace App\Http\Requests\V2;

use App\Models\SmtpSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmtpSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $provider = SmtpSetting::PROVIDERS[$this->input('provider')] ?? null;
        $username = trim((string) $this->input('username'));

        // Most providers sign in with the sender address, so the username is only asked when it differs
        if ($username === '' && $provider && $provider['username'] !== null) {
            $username = $provider['username'] === 'email'
                ? trim((string) $this->input('from_address'))
                : $provider['username'];
        }

        $password = $this->input('password');
        // Google/Yahoo display app passwords in groups of four; the spaces aren't part of it
        if (is_string($password) && in_array($this->input('provider'), ['gmail', 'yahoo'], true)) {
            $password = preg_replace('/\s+/', '', $password);
        }

        $this->merge([
            'username'     => $username,
            'password'     => $password,
            'host'         => trim((string) $this->input('host')),
            'from_address' => trim((string) $this->input('from_address')),
            'is_active'    => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $isEditing = $this->route('smtpSetting') !== null || $this->filled('smtp_setting_id');

        return [
            'provider'     => ['required', Rule::in(array_keys(SmtpSetting::PROVIDERS))],
            'name'         => ['nullable', 'string', 'max:100'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name'    => ['nullable', 'string', 'max:100'],
            'username'     => ['required', 'string', 'max:255'],
            'password'     => [$isEditing ? 'nullable' : 'required', 'string', 'max:1000'],
            'host'         => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-]+$/'],
            'port'         => ['required', 'integer', 'between:1,65535'],
            'encryption'   => ['required', Rule::in(array_keys(SmtpSetting::ENCRYPTIONS))],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Enter the SMTP username for this provider.',
            'password.required' => 'Enter the password so we can sign in to your email account.',
            'host.required'     => 'Enter the server address, e.g. mail.yourdomain.com (under Advanced settings).',
            'host.regex'        => 'The server address should look like smtp.example.com, without http:// or spaces.',
        ];
    }

    public function attributes(): array
    {
        return [
            'from_address' => 'email address',
            'from_name'    => 'sender name',
            'host'         => 'server address',
        ];
    }

    /**
     * Validated attributes ready for the model. A blank password on edit keeps the stored one.
     */
    public function settingData(): array
    {
        $validated = $this->validated();

        $data = [
            'provider'     => $validated['provider'],
            'from_address' => $validated['from_address'],
            'from_name'    => $validated['from_name'] ?? null,
            'username'     => $validated['username'],
            'host'         => $validated['host'],
            'port'         => (int) $validated['port'],
            'encryption'   => $validated['encryption'],
            'is_active'    => (bool) ($validated['is_active'] ?? false),
            'name'         => filled($validated['name'] ?? null)
                ? $validated['name']
                : SmtpSetting::PROVIDERS[$validated['provider']]['label'] . ' · ' . $validated['from_address'],
        ];

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        return $data;
    }
}
