<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\CustomerLoginRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Company $company): View
    {
        return view('portal.auth.login', compact('company'));
    }

    public function store(CustomerLoginRequest $request, Company $company): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route('portal.dashboard', ['company' => $company->slug]);
    }

    public function destroy(Request $request, Company $company): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login', ['company' => $company->slug]);
    }
}
