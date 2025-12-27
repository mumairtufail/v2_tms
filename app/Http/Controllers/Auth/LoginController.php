<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/welcome';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

     /**
     * Override the redirectTo method to determine the redirect URL
     * based on the user's role.
     *
     * @return string
     */
    protected function redirectTo()
    {
        // Check if the user is authenticated and has an admin role
        // if (Auth::check() && Auth::user()->is_admin) {
            return route('admin.dashboard');
        // }

        // // Check if the user has a company assigned
        // if (Auth::check() && Auth::user()->company_id !== null) {
            // return route('company.dashboard');  
        // }

        // Fallback to the default redirect
        return $this->redirectTo;
    }
}
