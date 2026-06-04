<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function index(Company $company)
    {
        return view('v2.company.settings.index', compact('company'));
    }

    public function branding(Company $company)
    {
        return view('v2.company.settings.branding', compact('company'));
    }

    public function updateBranding(Request $request, Company $company)
    {
        $validated = $request->validate([
            'logo_light' => ['nullable', 'image', 'max:3072'],
            'logo_dark' => ['nullable', 'image', 'max:3072'],
            'logo_icon' => ['nullable', 'image', 'max:2048'],
        ]);

        $fields = [
            'logo_light' => 'logo_light',
            'logo_dark'  => 'logo_dark',
        ];

        foreach ($fields as $inputKey => $column) {
            if (! $request->hasFile($inputKey)) {
                continue;
            }

            if ($company->{$column}) {
                Storage::disk('public')->delete($company->{$column});
            }

            $file      = $request->file($inputKey);
            $extension = $file->getClientOriginalExtension();
            $filename  = $column . '_' . time() . '.' . $extension;
            $path      = $file->storeAs('companies/' . $company->slug . '/branding', $filename, 'public');
            $company->{$column} = $path;
        }

        $company->save();

        return redirect()
            ->route('v2.settings.branding', ['company' => $company->slug])
            ->with('success', 'Branding updated successfully.');
    }
}
