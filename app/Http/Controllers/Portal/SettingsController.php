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
        $customer = Auth::guard('customer')->user();

        return view('portal.settings.index', compact('company', 'customer'));
    }

    public function updateProfile(UpdatePortalProfileRequest $request, Company $company): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $customer->update($request->validated());

        Toast::success('Profile updated successfully.');

        return redirect()->route('portal.settings', ['company' => $company->slug]);
    }

    public function updateSettings(UpdatePortalSettingsRequest $request, Company $company): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $customer->update($request->validated());

        Toast::success('Settings updated successfully.');

        return redirect()->route('portal.settings', ['company' => $company->slug]);
    }
}
