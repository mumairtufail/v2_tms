<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\SmtpSettingRequest;
use App\Models\Company;
use App\Models\SmtpSetting;
use App\Services\MailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmtpSettingController extends Controller
{
    public function __construct(protected MailService $mailService)
    {
    }

    public function index(Company $company)
    {
        $settings = SmtpSetting::forCompany($company->id)
            ->orderByDesc('is_active')
            ->latest()
            ->get();

        return view('v2.company.settings.smtp.index', compact('company', 'settings'));
    }

    public function create(Company $company)
    {
        return view('v2.company.settings.smtp.form', [
            'company' => $company,
            'setting' => null,
            'isFirst' => ! SmtpSetting::forCompany($company->id)->exists(),
        ]);
    }

    public function store(SmtpSettingRequest $request, Company $company)
    {
        $data = $request->settingData();
        // The first account is always used, otherwise nothing would send through it
        $makeActive = $data['is_active'] || ! SmtpSetting::forCompany($company->id)->exists();

        $setting = SmtpSetting::create(array_merge($data, [
            'company_id' => $company->id,
            'is_active'  => false,
        ]));

        if ($makeActive) {
            $setting->activate();
        }

        return redirect()
            ->route('v2.settings.smtp.show', [$company, $setting])
            ->with('success', $makeActive
                ? 'Email account saved and set as active. Send a test email to confirm it works.'
                : 'Email account saved. Send a test email to confirm it works.');
    }

    public function show(Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        return view('v2.company.settings.smtp.show', [
            'company' => $company,
            'setting' => $smtpSetting,
        ]);
    }

    public function edit(Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        return view('v2.company.settings.smtp.form', [
            'company' => $company,
            'setting' => $smtpSetting,
            'isFirst' => false,
        ]);
    }

    public function update(SmtpSettingRequest $request, Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        $data = $request->settingData();
        $makeActive = $data['is_active'];
        unset($data['is_active']);

        $smtpSetting->fill($data);

        // A previous test result says nothing about new connection details
        if ($smtpSetting->isDirty(['host', 'port', 'encryption', 'username', 'password', 'from_address'])) {
            $smtpSetting->forceFill([
                'last_tested_at'   => null,
                'last_test_passed' => null,
                'last_test_error'  => null,
            ]);
        }

        if (! $makeActive) {
            $smtpSetting->is_active = false;
        }

        $smtpSetting->save();

        if ($makeActive) {
            $smtpSetting->activate();
        }

        return redirect()
            ->route('v2.settings.smtp.show', [$company, $smtpSetting])
            ->with('success', 'Email account updated.');
    }

    public function destroy(Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        $wasActive = $smtpSetting->is_active;
        $smtpSetting->delete();

        return redirect()
            ->route('v2.settings.smtp.index', $company)
            ->with($wasActive ? 'warning' : 'success', $wasActive
                ? 'Email account deleted. It was your active account, so set another one as active to keep sending emails.'
                : 'Email account deleted.');
    }

    public function activate(Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        $smtpSetting->activate();

        return back()->with('success', "Emails will now be sent from {$smtpSetting->from_address}.");
    }

    public function deactivate(Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        $smtpSetting->forceFill(['is_active' => false])->save();

        return back()->with('warning', 'Account deactivated. Emails will use the system default until you set another account as active.');
    }

    /**
     * Send a test email with a saved account.
     */
    public function test(Request $request, Company $company, SmtpSetting $smtpSetting)
    {
        $this->ensureBelongsToCompany($company, $smtpSetting);

        $validated = $request->validate(['test_to' => ['required', 'email']], [], ['test_to' => 'recipient email']);

        $error = $this->mailService->sendTest($smtpSetting, $validated['test_to']);

        return $error
            ? back()->withInput()->with('error', $error)
            : back()->with('success', "Test email sent to {$validated['test_to']}. Check the inbox (and spam folder).");
    }

    /**
     * Send a test email with the unsaved values from the create/edit form.
     */
    public function testDraft(SmtpSettingRequest $request, Company $company): JsonResponse
    {
        $validated = $request->validate(['test_to' => ['required', 'email']], [], ['test_to' => 'recipient email']);

        $data = $request->settingData();
        unset($data['is_active']);

        if (! isset($data['password']) && $request->filled('smtp_setting_id')) {
            $existing = SmtpSetting::forCompany($company->id)->findOrFail($request->integer('smtp_setting_id'));
            $data['password'] = $existing->password;
        }

        $draft = new SmtpSetting($data);
        $error = $this->mailService->sendTest($draft, $validated['test_to']);

        return response()->json([
            'ok'      => $error === null,
            'message' => $error ?? "It works! A test email was sent to {$validated['test_to']}.",
        ]);
    }

    private function ensureBelongsToCompany(Company $company, SmtpSetting $smtpSetting): void
    {
        abort_unless((int) $smtpSetting->company_id === (int) $company->id, 404);
    }
}
