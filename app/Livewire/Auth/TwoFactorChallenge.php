<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallenge extends Component
{
    public string $code = '';
    public string $recovery_code = '';
    public bool $use_recovery = false;

    public function mount(): void
    {
        if (!Session::has('2fa.user_id')) {
            $this->redirectRoute('login');
        }
    }

    public function authenticate(): void
    {
        $userId = Session::get('2fa.user_id');

        if (!$userId) {
            $this->redirect(route('login'));
            return;
        }

        $user = User::find($userId);

        if (!$user) {
            Session::forget('2fa.user_id');
            $this->redirect(route('login'));
            return;
        }

        if ($this->use_recovery) {
            $this->validateRecoveryCode($user);
        } else {
            $this->validateTotp($user);
        }
    }

    private function validateTotp(User $user): void
    {
        $this->validate(['code' => 'required|digits:6']);

        try {
            $google2fa = new Google2FA();
            $secret    = decrypt($user->two_factor_secret);
            $valid     = $google2fa->verifyKey($secret, $this->code, 2);
        } catch (\Throwable $e) {
            $this->addError('code', 'Verification failed. Please try again.');
            return;
        }

        if (!$valid) {
            $this->addError('code', 'The code is invalid or has expired. Please try again.');
            return;
        }

        $this->completeLogin($user);
    }

    private function validateRecoveryCode(User $user): void
    {
        $this->validate(['recovery_code' => 'required|string']);

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $index = array_search($this->recovery_code, $codes);

        if ($index === false) {
            $this->addError('recovery_code', 'The recovery code is invalid.');
            return;
        }

        // Remove used code
        unset($codes[$index]);
        $user->update(['two_factor_recovery_codes' => encrypt(json_encode(array_values($codes)))]);

        $this->completeLogin($user);
    }

    private function completeLogin(User $user): void
    {
        $remember = Session::get('2fa.remember', false);
        Session::forget(['2fa.user_id', '2fa.remember']);

        Auth::login($user, $remember);

        $user->update([
            'last_login_at' => now(),
            'remember_token_expires_at' => $remember ? now()->addDays(30) : null,
        ]);

        if ($user->is_super_admin) {
            $this->redirect(route('admin.dashboard'));
        } elseif ($user->company) {
            $this->redirect(route('v2.dashboard', ['company' => $user->company->slug]));
        } else {
            $this->redirect('/');
        }
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge')
            ->layout('components.guest-layout');
    }
}
