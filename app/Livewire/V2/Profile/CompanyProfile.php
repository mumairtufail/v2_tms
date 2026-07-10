<?php

namespace App\Livewire\V2\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class CompanyProfile extends Component
{
    // Profile fields
    public string $f_name  = '';
    public string $l_name  = '';
    public string $email   = '';
    public string $phone   = '';
    public bool   $email_notifications = true;

    // Password fields
    public string $current_password          = '';
    public string $new_password              = '';
    public string $new_password_confirmation = '';
    public bool   $show_current = false;
    public bool   $show_new     = false;
    public bool   $show_confirm = false;

    // Password strength
    public int   $password_strength = 0;
    public array $password_checks   = [
        'length'    => false,
        'uppercase' => false,
        'lowercase' => false,
        'number'    => false,
        'special'   => false,
    ];

    // 2FA — qr_code_svg is NOT stored in state; computed from pending_secret each render
    public bool   $two_factor_enabled   = false;
    public bool   $show_2fa_setup       = false;
    public string $two_factor_code      = '';
    public string $pending_secret       = '';
    public array  $recovery_codes       = [];
    public bool   $show_recovery_codes  = false;
    public bool   $show_disable_confirm = false;
    public string $disable_password     = '';

    protected string $company_slug = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->f_name              = $user->f_name  ?? '';
        $this->l_name              = $user->l_name  ?? '';
        $this->email               = $user->email   ?? '';
        $this->phone               = $user->phone   ?? '';
        $this->email_notifications = (bool) $user->email_notifications;
        $this->two_factor_enabled  = (bool) $user->two_factor_enabled;
        $this->company_slug        = $user->company?->slug ?? '';

        // Recover a pending 2FA setup if the component remounted mid-flow
        if (Session::has('2fa_pending_secret')) {
            $this->pending_secret = Session::get('2fa_pending_secret');
            $this->show_2fa_setup = true;
        }

        Log::channel('2fa')->info('[CompanyProfile] mount', [
            'user_id'            => $user->id,
            'two_factor_enabled' => $this->two_factor_enabled,
        ]);
    }

    // ─── Real-time password strength ─────────────────────────────────────────

    public function updatedNewPassword(string $value): void
    {
        $this->password_checks = [
            'length'    => strlen($value) >= 8,
            'uppercase' => (bool) preg_match('/[A-Z]/', $value),
            'lowercase' => (bool) preg_match('/[a-z]/', $value),
            'number'    => (bool) preg_match('/[0-9]/', $value),
            'special'   => (bool) preg_match('/[\W_]/', $value),
        ];
        $this->password_strength = count(array_filter($this->password_checks));
    }

    public function generatePassword(): void
    {
        $chars    = 'abcdefghijklmnopqrstuvwxyz';
        $upper    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers  = '0123456789';
        $special  = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = $chars[random_int(0, strlen($chars) - 1)]
            . $upper[random_int(0, strlen($upper) - 1)]
            . $numbers[random_int(0, strlen($numbers) - 1)]
            . $special[random_int(0, strlen($special) - 1)]
            . Str::random(8);
        $password = str_shuffle($password);

        $this->new_password              = $password;
        $this->new_password_confirmation = $password;
        $this->show_new                  = true;
        $this->show_confirm              = true;
        $this->updatedNewPassword($password);
    }

    // ─── Save profile ────────────────────────────────────────────────────────

    public function saveProfile(): void
    {
        $this->validate([
            'f_name' => 'required|string|max:100',
            'l_name' => 'required|string|max:100',
            'phone'  => 'nullable|string|max:30',
        ]);

        Auth::user()->update([
            'f_name'              => $this->f_name,
            'l_name'              => $this->l_name,
            'name'                => $this->f_name . ' ' . $this->l_name,
            'phone'               => $this->phone,
            'email_notifications' => $this->email_notifications,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Profile updated successfully.');
    }

    // ─── Update password ─────────────────────────────────────────────────────

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        Auth::user()->update(['password' => Hash::make($this->new_password)]);

        $this->current_password          = '';
        $this->new_password              = '';
        $this->new_password_confirmation = '';
        $this->password_strength         = 0;
        $this->password_checks           = array_fill_keys(array_keys($this->password_checks), false);

        $this->dispatch('notify', type: 'success', message: 'Password updated successfully.');
    }

    // ─── 2FA setup ───────────────────────────────────────────────────────────

    public function initTwoFactor(): void
    {
        Log::channel('2fa')->info('[CompanyProfile] initTwoFactor called', ['user_id' => Auth::id()]);

        $google2fa            = new Google2FA();
        $this->pending_secret = $google2fa->generateSecretKey();
        $this->show_2fa_setup = true;
        $this->two_factor_code = '';

        // Persist the secret server-side so it survives any Livewire remount
        Session::put('2fa_pending_secret', $this->pending_secret);

        Log::channel('2fa')->info('[CompanyProfile] Secret generated', [
            'user_id'       => Auth::id(),
            'secret_length' => strlen($this->pending_secret),
        ]);
    }

    public function confirmTwoFactor(): void
    {
        Log::channel('2fa')->info('[CompanyProfile] confirmTwoFactor called', [
            'user_id'    => Auth::id(),
            'code'       => $this->two_factor_code,
            'has_secret' => !empty($this->pending_secret),
            'secret_len' => strlen($this->pending_secret),
        ]);

        $this->validate(['two_factor_code' => 'required|digits:6']);

        Log::channel('2fa')->info('[CompanyProfile] Validation passed');

        $secret = $this->pending_secret ?: Session::get('2fa_pending_secret');

        if (empty($secret)) {
            Log::channel('2fa')->warning('[CompanyProfile] pending_secret is empty — session lost');
            $this->addError('two_factor_code', 'Setup session expired. Please click "Enable 2FA" again.');
            $this->show_2fa_setup = false;
            return;
        }

        try {
            Log::channel('2fa')->info('[CompanyProfile] Calling verifyKey', [
                'secret' => substr($secret, 0, 4) . '...',
                'code'   => $this->two_factor_code,
                'window' => 2,
            ]);

            $google2fa = new Google2FA();
            $valid     = $google2fa->verifyKey($secret, $this->two_factor_code, 2);

            Log::channel('2fa')->info('[CompanyProfile] verifyKey result', ['valid' => $valid]);
        } catch (\Throwable $e) {
            Log::channel('2fa')->error('[CompanyProfile] verifyKey threw exception', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'class'   => get_class($e),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->addError('two_factor_code', 'Verification error: ' . $e->getMessage());
            return;
        }

        if (!$valid) {
            Log::channel('2fa')->info('[CompanyProfile] Code invalid', ['user_id' => Auth::id()]);
            $this->addError('two_factor_code', 'Invalid code. Please try again.');
            return;
        }

        Log::channel('2fa')->info('[CompanyProfile] Code valid — enabling 2FA', ['user_id' => Auth::id()]);

        $codes = collect(range(1, 8))
            ->map(fn() => strtoupper(Str::random(4) . '-' . Str::random(4)))
            ->toArray();

        Auth::user()->update([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            'two_factor_confirmed_at'   => now(),
            'two_factor_enabled'        => true,
        ]);

        Session::forget('2fa_pending_secret');

        $this->two_factor_enabled   = true;
        $this->show_2fa_setup       = false;
        $this->recovery_codes       = $codes;
        $this->show_recovery_codes  = true;
        $this->pending_secret       = '';
        $this->two_factor_code      = '';

        Log::channel('2fa')->info('[CompanyProfile] 2FA enabled successfully', ['user_id' => Auth::id()]);

        $this->dispatch('notify', type: 'success', message: 'Two-factor authentication enabled.');
    }

    public function cancelTwoFactor(): void
    {
        Log::channel('2fa')->info('[CompanyProfile] cancelTwoFactor called', ['user_id' => Auth::id()]);

        Session::forget('2fa_pending_secret');
        $this->show_2fa_setup  = false;
        $this->pending_secret  = '';
        $this->two_factor_code = '';
    }

    public function disableTwoFactor(): void
    {
        Log::channel('2fa')->info('[CompanyProfile] disableTwoFactor called', ['user_id' => Auth::id()]);

        $this->validate(['disable_password' => 'required']);

        if (!Hash::check($this->disable_password, Auth::user()->password)) {
            Log::channel('2fa')->warning('[CompanyProfile] Wrong password on disable', ['user_id' => Auth::id()]);
            $this->addError('disable_password', 'Incorrect password.');
            return;
        }

        Auth::user()->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_enabled'        => false,
        ]);

        $this->two_factor_enabled   = false;
        $this->show_disable_confirm = false;
        $this->disable_password     = '';

        Log::channel('2fa')->info('[CompanyProfile] 2FA disabled', ['user_id' => Auth::id()]);

        $this->dispatch('notify', type: 'success', message: 'Two-factor authentication disabled.');
    }

    public function viewRecoveryCodes(): void
    {
        $user = Auth::user();
        if ($user->two_factor_recovery_codes) {
            $this->recovery_codes      = json_decode(decrypt($user->two_factor_recovery_codes), true);
            $this->show_recovery_codes = true;
        }
    }

    public function render()
    {
        $qrCodeSvg = '';
        $secret    = $this->pending_secret ?: Session::get('2fa_pending_secret');

        if (!empty($secret)) {
            try {
                $google2fa = new Google2FA();
                $qrUrl = $google2fa->getQRCodeUrl(
                    config('app.name'),
                    Auth::user()->email,
                    $secret
                );

                $writer = new \BaconQrCode\Writer(
                    new \BaconQrCode\Renderer\ImageRenderer(
                        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                    )
                );

                $qrCodeSvg = base64_encode($writer->writeString($qrUrl));
            } catch (\Throwable $e) {
                Log::channel('2fa')->error('[CompanyProfile] QR generation failed', [
                    'user_id' => Auth::id(),
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return view('livewire.v2.profile.company-profile', [
            'user'      => Auth::user()->load('roles', 'company'),
            'company'   => Auth::user()->company,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }
}
