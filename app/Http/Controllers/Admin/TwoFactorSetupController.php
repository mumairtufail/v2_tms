<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSetupController extends Controller
{
    public function confirm(Request $request)
    {
        $request->validate(['two_factor_code' => 'required|digits:6']);

        $secret = Session::get('2fa_pending_secret');

        if (empty($secret)) {
            Log::channel('2fa')->warning('[TwoFactorSetup] Session secret missing', ['user_id' => Auth::id()]);
            return redirect()->route('admin.profile')
                ->with('error', 'Setup session expired. Please click "Enable 2FA" again.');
        }

        Log::channel('2fa')->info('[TwoFactorSetup] confirm called', [
            'user_id' => Auth::id(),
            'code'    => $request->two_factor_code,
        ]);

        try {
            $google2fa = new Google2FA();
            $valid     = $google2fa->verifyKey($secret, $request->two_factor_code, 2);
        } catch (\Throwable $e) {
            Log::channel('2fa')->error('[TwoFactorSetup] verifyKey failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->withErrors(['two_factor_code' => 'Verification error. Please try again.']);
        }

        if (!$valid) {
            Log::channel('2fa')->info('[TwoFactorSetup] Code invalid', ['user_id' => Auth::id()]);
            return back()->withErrors(['two_factor_code' => 'Invalid code. Please try again.']);
        }

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
        Session::put('2fa_recovery_codes', $codes);

        Log::channel('2fa')->info('[TwoFactorSetup] 2FA enabled', ['user_id' => Auth::id()]);

        return redirect()->route('admin.profile')->with('success', 'Two-factor authentication enabled.');
    }

    public function cancel()
    {
        Session::forget('2fa_pending_secret');
        Log::channel('2fa')->info('[TwoFactorSetup] cancelled', ['user_id' => Auth::id()]);
        return redirect()->route('admin.profile');
    }
}
