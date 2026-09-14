<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // The email itself is queued by User::sendPasswordResetNotification via MailService
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('We couldn\'t send the reset email right now. Please try again in a few minutes or contact your administrator.')]);
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        // Same response whether or not the account exists, so this form can't be used to discover registered emails
        return back()->with('status', __('If an account exists for :email, a password reset link is on its way. It can take a minute to arrive, so check your spam folder too.', [
            'email' => $request->input('email'),
        ]));
    }
}
