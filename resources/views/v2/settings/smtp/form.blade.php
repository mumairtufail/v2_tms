@extends('v2.layouts.app')

@section('title', $setting ? 'Edit Email Account' : 'Add Email Account')

@section('content')
@php
    $providers = \App\Models\SmtpSetting::PROVIDERS;
    $providerKey = old('provider', $setting->provider ?? 'gmail');
    $providerMeta = $providers[$providerKey] ?? $providers['custom'];

    // Blank means "same as the email address", which keeps the form short for most providers
    if ($setting) {
        $savedUsername = $setting->username === $setting->from_address ? '' : $setting->username;
    } else {
        $savedUsername = in_array($providerMeta['username'], ['email', null], true) ? '' : $providerMeta['username'];
    }

    $initial = [
        'provider'     => $providerKey,
        'name'         => old('name', $setting->name ?? ''),
        'from_address' => old('from_address', $setting->from_address ?? ''),
        'from_name'    => old('from_name', $setting ? $setting->from_name : $brandName),
        'username'     => old('username', $savedUsername),
        'host'         => old('host', $setting->host ?? $providerMeta['host']),
        'port'         => (int) old('port', $setting->port ?? $providerMeta['port']),
        'encryption'   => old('encryption', $setting->encryption ?? $providerMeta['encryption']),
        'is_active'    => $isFirst ? true : (bool) old('is_active', $setting ? $setting->is_active : true),
    ];

    $showAdvanced = $providerKey === 'custom'
        || $errors->hasAny(['host', 'port', 'encryption', 'name'])
        || ($errors->has('username') && $providerMeta['username'] !== null);

    $formConfig = [
        'providers'    => $providers,
        'encryptions'  => \App\Models\SmtpSetting::ENCRYPTIONS,
        'initial'      => $initial,
        'showAdvanced' => $showAdvanced,
        'testUrl'      => $smtpUrl('test-draft'),
        'testTo'       => auth()->user()->email,
    ];

    $inputClass = 'w-full px-3.5 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors';
    $labelClass = 'block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300';
    $hintClass = 'mt-1.5 text-xs text-gray-500 dark:text-gray-400';
    $cardClass = 'rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#0B1120] shadow-sm';
@endphp

<div class="space-y-5" x-data="smtpForm(@js($formConfig))">
    <x-v2-breadcrumb :items="[
        ['label' => 'Settings', 'url' => $settingsIndexUrl],
        ['label' => 'Email (SMTP)', 'url' => $smtpUrl('index')],
        ['label' => $setting ? 'Edit' : 'Add account']
    ]" />

    <div class="flex items-center gap-4">
        <a href="{{ $setting ? $smtpUrl('show', $setting) : $smtpUrl('index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <x-page-header
            :title="$setting ? 'Edit email account' : 'Add email account'"
            :description="$setting ? 'Update how emails are sent from ' . $setting->from_address . '.' : 'Choose your provider and sign in. We fill in the technical details for you.'"
        />
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <form x-ref="form" method="POST"
              action="{{ $setting ? $smtpUrl('update', $setting) : $smtpUrl('store') }}"
              @submit="submitting = true"
              class="space-y-5 lg:col-span-2">
            @csrf
            @if($setting)
                @method('PUT')
                <input type="hidden" name="smtp_setting_id" value="{{ $setting->id }}">
            @endif
            <input type="hidden" name="provider" :value="form.provider">
            <input type="hidden" name="username" :value="form.username">
            <input type="hidden" name="encryption" :value="form.encryption">
            <input type="hidden" name="is_active" :value="form.is_active ? 1 : 0">

            {{-- Step 1: provider --}}
            <section class="{{ $cardClass }} p-5">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">1</span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Which email service do you use?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Not listed? Choose "Other".</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($providers as $key => $provider)
                        <button type="button" @click="selectProvider('{{ $key }}')"
                                class="relative flex items-center gap-2.5 rounded-lg border p-2.5 text-left transition-all focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                                :class="form.provider === '{{ $key }}'
                                    ? 'border-primary-500 bg-primary-50/60 dark:border-primary-500 dark:bg-primary-900/20'
                                    : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900'">
                            @include('v2.settings.smtp.partials.provider-badge', ['provider' => $key])
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $provider['label'] }}</p>
                                <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">{{ $provider['subtitle'] }}</p>
                            </div>
                            <span x-show="form.provider === '{{ $key }}'" x-cloak class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary-600 text-white">
                                <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </button>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('provider')" class="mt-2" />
            </section>

            {{-- Step 2: sign in --}}
            <section class="{{ $cardClass }} p-5">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">2</span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Sign in to your email account</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">These are the details you use to log in to <span x-text="meta.label"></span>.</p>
                    </div>
                </div>

                <div class="mb-5 flex items-start gap-2.5 rounded-lg border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-900/20 p-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs leading-5 text-blue-800 dark:text-blue-300">
                        <span x-text="meta.help"></span>
                        <a x-show="meta.help_url" x-cloak :href="meta.help_url" target="_blank" rel="noopener noreferrer" class="ml-1 whitespace-nowrap font-semibold underline hover:no-underline">Show me how →</a>
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="from_address" class="{{ $labelClass }}">Email address <span class="text-red-500">*</span></label>
                        <input id="from_address" name="from_address" type="email" x-model="form.from_address" required autocomplete="off" placeholder="you@company.com" class="{{ $inputClass }}">
                        <p class="{{ $hintClass }}">Emails will be sent from this address.</p>
                        <x-input-error :messages="$errors->get('from_address')" class="mt-1" />
                    </div>

                    <div>
                        <label for="from_name" class="{{ $labelClass }}">Sender name <span class="font-normal text-gray-400">(optional)</span></label>
                        <input id="from_name" name="from_name" type="text" x-model="form.from_name" maxlength="100" placeholder="{{ $brandName }}" class="{{ $inputClass }}">
                        <p class="{{ $hintClass }}">What people see in their inbox.</p>
                        <x-input-error :messages="$errors->get('from_name')" class="mt-1" />
                    </div>

                    <div x-show="needsUsername" x-cloak class="md:col-span-2">
                        <label for="username_visible" class="{{ $labelClass }}">SMTP username <span class="text-red-500">*</span></label>
                        <input id="username_visible" type="text" x-model="form.username" autocomplete="off" placeholder="Provided by your email service" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('username')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="password" class="{{ $labelClass }}">
                            <span x-text="meta.password_label">Password</span>
                            @unless($setting)<span class="text-red-500">*</span>@endunless
                        </label>
                        <div class="relative">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password"
                                   @unless($setting) required @endunless
                                   placeholder="{{ $setting ? 'Leave blank to keep the current password' : '' }}"
                                   class="{{ $inputClass }} pr-11">
                            <button type="button" @click="showPassword = !showPassword" :title="showPassword ? 'Hide' : 'Show'"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        <p class="{{ $hintClass }} flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Stored encrypted and never shown again.
                        </p>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                </div>
            </section>

            {{-- Advanced --}}
            <section class="{{ $cardClass }}">
                <button type="button" @click="showAdvanced = !showAdvanced" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Advanced settings</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400"
                           x-text="form.host ? `${form.host} · port ${form.port} · ${encryptionLabel}` : 'Server address, port and encryption'"></p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <span x-show="form.provider !== 'custom' && !showAdvanced" class="hidden sm:inline text-xs text-gray-400 dark:text-gray-500">Filled in for you</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform" :class="showAdvanced && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                <div x-show="showAdvanced" x-cloak x-transition.opacity class="space-y-5 border-t border-gray-100 dark:border-gray-800 px-5 pb-5 pt-4">
                    <p x-show="form.provider === 'custom'" class="text-xs text-gray-500 dark:text-gray-400">
                        Not sure what to enter? Ask whoever manages your email for the "SMTP settings".
                    </p>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label for="host" class="{{ $labelClass }}">Server address <span class="text-red-500">*</span></label>
                            <input id="host" name="host" type="text" x-model="form.host" autocomplete="off" placeholder="smtp.yourdomain.com" class="{{ $inputClass }}">
                            <x-input-error :messages="$errors->get('host')" class="mt-1" />
                        </div>
                        <div>
                            <label for="port" class="{{ $labelClass }}">Port <span class="text-red-500">*</span></label>
                            <input id="port" name="port" type="number" min="1" max="65535" x-model.number="form.port" class="{{ $inputClass }}">
                            <x-input-error :messages="$errors->get('port')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <span class="{{ $labelClass }}">Encryption</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach([
                                'tls'  => ['TLS', 'Recommended · 587'],
                                'ssl'  => ['SSL', 'Port 465'],
                                'none' => ['None', 'Not secure · 25'],
                            ] as $value => [$encLabel, $encHint])
                                <button type="button" @click="setEncryption('{{ $value }}')"
                                        class="rounded-lg border px-3 py-2 text-left transition-all"
                                        :class="form.encryption === '{{ $value }}'
                                            ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-900/20'
                                            : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900'">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $encLabel }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $encHint }}</p>
                                </button>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('encryption')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div x-show="!needsUsername">
                            <label for="username_override" class="{{ $labelClass }}">Username</label>
                            <input id="username_override" type="text" x-model="form.username" autocomplete="off" placeholder="Same as email address" class="{{ $inputClass }}">
                            <p class="{{ $hintClass }}">Only change this if your login differs from the email address.</p>
                        </div>
                        <div>
                            <label for="name" class="{{ $labelClass }}">Account nickname</label>
                            <input id="name" name="name" type="text" x-model="form.name" maxlength="100" :placeholder="namePlaceholder" class="{{ $inputClass }}">
                            <p class="{{ $hintClass }}">Helps you tell accounts apart.</p>
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </section>

            {{-- Active toggle --}}
            <section class="{{ $cardClass }} flex items-start justify-between gap-4 p-5">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Use this account to send emails</p>
                    <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        @if($isFirst)
                            This is your first account, so it will be used automatically.
                        @else
                            Only one account can be active. Turning this on switches off the account currently in use.
                        @endif
                    </p>
                </div>
                <button type="button" role="switch" :aria-checked="form.is_active.toString()"
                        @click="form.is_active = !form.is_active"
                        @if($isFirst) disabled @endif
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:cursor-not-allowed disabled:opacity-60"
                        :class="form.is_active ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                          :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ $setting ? $smtpUrl('show', $setting) : $smtpUrl('index') }}"
                   class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-500/20 transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ $setting ? 'Save changes' : 'Save account' }}
                </button>
            </div>
        </form>

        {{-- Sidebar --}}
        <aside class="space-y-5">
            <div class="{{ $cardClass }} p-5">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Try it before saving</h3>
                </div>
                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    We'll sign in with the details you entered and send a short test email. Nothing is saved.
                </p>

                <label for="test_to" class="mt-4 block mb-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">Send test to</label>
                <input id="test_to" type="email" x-model="testTo" class="{{ $inputClass }}">

                <button type="button" @click="sendTest()" :disabled="testing || !testTo"
                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20 px-4 py-2 text-sm font-semibold text-primary-700 dark:text-primary-300 transition-colors hover:bg-primary-100 dark:hover:bg-primary-900/40 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg x-show="testing" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="testing ? 'Sending… this can take a few seconds' : 'Send test email'">Send test email</span>
                </button>

                <div x-show="testResult" x-cloak x-transition.opacity
                     class="mt-3 flex items-start gap-2 rounded-lg border p-3 text-xs leading-5"
                     :class="testResult?.ok
                        ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300'
                        : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300'">
                    <svg x-show="testResult?.ok" class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="!testResult?.ok" class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="testResult?.message"></span>
                </div>
            </div>

            <div class="{{ $cardClass }} p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">How it works</h3>
                <ul class="mt-3 space-y-3 text-xs leading-5 text-gray-600 dark:text-gray-400">
                    <li class="flex gap-2.5">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500"></span>
                        Emails like password resets are queued and sent in the background through your active account.
                    </li>
                    <li class="flex gap-2.5">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500"></span>
                        You can save several accounts, but only one is active at a time. Switch anytime.
                    </li>
                    <li class="flex gap-2.5">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500"></span>
                        If no account is active, the system's default mail settings are used.
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function smtpForm(config) {
    return {
        providers: config.providers,
        encryptions: config.encryptions,
        form: config.initial,
        showAdvanced: config.showAdvanced,
        showPassword: false,
        submitting: false,
        testTo: config.testTo,
        testing: false,
        testResult: null,

        get meta() {
            return this.providers[this.form.provider] ?? this.providers.custom;
        },
        get needsUsername() {
            return this.meta.username === null;
        },
        get encryptionLabel() {
            return this.encryptions[this.form.encryption] ?? this.form.encryption;
        },
        get namePlaceholder() {
            return `${this.meta.label} · ${this.form.from_address || 'you@company.com'}`;
        },

        selectProvider(key) {
            if (this.form.provider === key) return;
            const preset = this.providers[key];
            this.form.provider = key;
            this.form.host = preset.host;
            this.form.port = preset.port;
            this.form.encryption = preset.encryption;
            this.form.username = preset.username && preset.username !== 'email' ? preset.username : '';
            this.showAdvanced = key === 'custom';
            this.testResult = null;
        },

        setEncryption(value) {
            this.form.encryption = value;
            // Follow the usual port for each mode unless the user typed something unusual
            const standardPorts = [25, 465, 587];
            if (standardPorts.includes(this.form.port)) {
                this.form.port = { tls: 587, ssl: 465, none: 25 }[value];
            }
        },

        async sendTest() {
            this.testing = true;
            this.testResult = null;

            const data = new FormData(this.$refs.form);
            data.delete('_method');
            data.set('test_to', this.testTo);

            try {
                const response = await fetch(config.testUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: data,
                });
                const json = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    const firstError = Object.values(json.errors ?? {}).flat()[0];
                    this.testResult = { ok: false, message: firstError ?? json.message ?? 'Please check the details you entered.' };
                } else if (response.status === 429) {
                    this.testResult = { ok: false, message: 'Too many tests in a short time. Please wait a minute and try again.' };
                } else if (!response.ok) {
                    this.testResult = { ok: false, message: 'Something went wrong on our side. Please try again.' };
                } else {
                    this.testResult = { ok: !!json.ok, message: json.message };
                }
            } catch (e) {
                this.testResult = { ok: false, message: 'Could not reach the server. Check your connection and try again.' };
            } finally {
                this.testing = false;
            }
        },
    };
}
</script>
@endpush
@endsection
