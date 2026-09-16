<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UpdatePortalProfileRequest;
use App\Http\Requests\Portal\UpdatePortalSettingsRequest;
use App\Models\Company;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Company $company): View
    {
        $contact = Auth::guard('customer')->user();
        $customer = app('current.customer');

        return view('portal.settings.index', compact('company', 'customer', 'contact'));
    }

    /** The signed-in person's own profile. */
    public function updateProfile(UpdatePortalProfileRequest $request, Company $company): RedirectResponse
    {
        Auth::guard('customer')->user()->update($request->validated());

        Toast::success('Profile updated successfully.');

        return redirect()->route('portal.settings', ['company' => $company->slug]);
    }

    /** Preferences for the whole customer account. */
    public function updateSettings(UpdatePortalSettingsRequest $request, Company $company): RedirectResponse
    {
        app('current.customer')->update($request->validated());

        Toast::success('Settings updated successfully.');

        return redirect()->route('portal.settings', ['company' => $company->slug]);
    }
}
