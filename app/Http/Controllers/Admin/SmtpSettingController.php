<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\SmtpSettingRequest;
use App\Models\SmtpSetting;
use App\Services\MailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Platform-wide email accounts. These rows carry a null company_id and are used
 * for super admin emails, plus as the fallback for any company that has not
 * connected an account of its own.
 */
class SmtpSettingController extends Controller
{
    public function __construct(protected MailService $mailService)
    {
    }

    public function index()
    {
        $settings = SmtpSetting::forCompany(null)
            ->orderByDesc('is_active')
            ->latest()
            ->get();

        return view('v2.settings.smtp.index', array_merge($this->viewData(), compact('settings')));
    }

    public function create()
    {
        return view('v2.settings.smtp.form', array_merge($this->viewData(), [
            'setting' => null,
            'isFirst' => ! SmtpSetting::forCompany(null)->exists(),
        ]));
    }

    public function store(SmtpSettingRequest $request)
    {
        $data = $request->settingData();
        // The first account is always used, otherwise nothing would send through it
        $makeActive = $data['is_active'] || ! SmtpSetting::forCompany(null)->exists();

        $setting = SmtpSetting::create(array_merge($data, [
            'company_id' => null,
            'is_active'  => false,
        ]));

        if ($makeActive) {
            $setting->activate();
        }

        return redirect()
            ->route('admin.settings.smtp.show', $setting)
            ->with('success', $makeActive
                ? 'Email account saved and set as active. Send a test email to confirm it works.'
                : 'Email account saved. Send a test email to confirm it works.');
    }

    public function show(SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

        return view('v2.settings.smtp.show', array_merge($this->viewData(), [
            'setting' => $smtpSetting,
        ]));
    }

    public function edit(SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

        return view('v2.settings.smtp.form', array_merge($this->viewData(), [
            'setting' => $smtpSetting,
            'isFirst' => false,
        ]));
    }

    public function update(SmtpSettingRequest $request, SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

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
            ->route('admin.settings.smtp.show', $smtpSetting)
            ->with('success', 'Email account updated.');
    }

    public function destroy(SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

        $wasActive = $smtpSetting->is_active;
        $smtpSetting->delete();

        return redirect()
            ->route('admin.settings.smtp.index')
            ->with($wasActive ? 'warning' : 'success', $wasActive
                ? 'Email account deleted. It was the active platform account, so set another one as active to keep sending emails.'
                : 'Email account deleted.');
    }

    public function activate(SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

        $smtpSetting->activate();

        return back()->with('success', "Platform emails will now be sent from {$smtpSetting->from_address}.");
    }

    public function deactivate(SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

        $smtpSetting->forceFill(['is_active' => false])->save();

        return back()->with('warning', 'Account deactivated. Emails will use the system default until you set another account as active.');
    }

    /**
     * Send a test email with a saved account.
     */
    public function test(Request $request, SmtpSetting $smtpSetting)
    {
        $this->ensureSystemAccount($smtpSetting);

        $validated = $request->validate(['test_to' => ['required', 'email']], [], ['test_to' => 'recipient email']);

        $error = $this->mailService->sendTest($smtpSetting, $validated['test_to']);

        return $error
            ? back()->withInput()->with('error', $error)
            : back()->with('success', "Test email sent to {$validated['test_to']}. Check the inbox (and spam folder).");
    }

    /**
     * Send a test email with the unsaved values from the create/edit form.
     */
    public function testDraft(SmtpSettingRequest $request): JsonResponse
    {
        $validated = $request->validate(['test_to' => ['required', 'email']], [], ['test_to' => 'recipient email']);

        $data = $request->settingData();
        unset($data['is_active']);

        if (! isset($data['password']) && $request->filled('smtp_setting_id')) {
            $existing = SmtpSetting::forCompany(null)->findOrFail($request->integer('smtp_setting_id'));
            $data['password'] = $existing->password;
        }

        $draft = new SmtpSetting($data);
        $error = $this->mailService->sendTest($draft, $validated['test_to']);

        return response()->json([
            'ok'      => $error === null,
            'message' => $error ?? "It works! A test email was sent to {$validated['test_to']}.",
        ]);
    }

    /**
     * Company accounts are managed inside their own portal, so they are out of
     * reach here even though the route binding would happily resolve them.
     */
    private function ensureSystemAccount(SmtpSetting $smtpSetting): void
    {
        abort_unless(is_null($smtpSetting->company_id), 404);
    }

    /**
     * The SMTP views are shared with the company portal, so they take their
     * URLs and branding from the controller rather than hardcoding a route group.
     */
    private function viewData(): array
    {
        return [
            'smtpUrl'          => fn (string $name, ...$params) => route("admin.settings.smtp.{$name}", $params),
            'settingsIndexUrl' => route('admin.settings.index'),
            'brandName'        => config('app.name'),
        ];
    }
}
