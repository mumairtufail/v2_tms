<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemSettingsController extends Controller
{
    public function index()
    {
        return view('v2.admin.settings.index');
    }

    public function branding()
    {
        $settings = SystemSetting::instance();
        return view('v2.admin.settings.branding', compact('settings'));
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'logo_light' => ['nullable', 'image', 'max:3072'],
            'logo_dark'  => ['nullable', 'image', 'max:3072'],
            'logo_icon'  => ['nullable', 'image', 'max:2048'],
        ]);

        $settings = SystemSetting::instance();

        $fields = ['logo_light', 'logo_dark'];

        foreach ($fields as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            if ($settings->{$field}) {
                Storage::disk('public')->delete($settings->{$field});
            }

            $file      = $request->file($field);
            $extension = $file->getClientOriginalExtension();
            $filename  = $field . '_' . time() . '.' . $extension;
            $settings->{$field} = $file->storeAs('system/branding', $filename, 'public');
        }

        $settings->save();

        return redirect()->route('admin.settings.branding')
            ->with('success', 'System branding updated successfully.');
    }
}
