<?php

namespace App\Http\Requests\Portal;

use App\Models\CustomerContact;
use App\Services\ActivityLog;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $company = app('current.company');

        $contact = CustomerContact::withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->where('email', strtolower(trim((string) $this->email)))
            ->where('portal_access', true)
            ->whereHas('customer', fn ($q) => $q->where('is_active', true)->where('is_deleted', false))
            ->first();

        if (!$contact || !$contact->password || !Hash::check($this->password, $contact->password)) {
            RateLimiter::hit($this->throttleKey());

            app(ActivityLog::class)->logAuth('portal.login.failed', [
                'allow_guest' => true,
                'email' => $this->input('email'),
                'company_id' => $company->id,
                'description' => 'Failed customer portal login attempt',
            ], false);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::guard('customer')->login($contact, $this->boolean('remember'));
        $contact->forceFill(['last_login_at' => now()])->saveQuietly();

        RateLimiter::clear($this->throttleKey());

        app(ActivityLog::class)->logAuth('portal.login.success', [
            'description' => 'Customer logged into portal',
            'email' => $contact->email,
            'customer_id' => $contact->customer_id,
            'company_id' => $company->id,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        $company = app('current.company');

        return Str::transliterate(
            Str::lower($this->string('email')).'|portal|'.$company->id.'|'.$this->ip()
        );
    }
}
