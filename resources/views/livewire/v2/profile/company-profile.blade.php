<div class="space-y-6">
    <x-v2-breadcrumb :items="[['label' => 'Profile']]" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <x-page-header title="My Profile" description="Manage your account settings and security" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ═══════════════════════════════════════════════════════════════
             LEFT COLUMN — Avatar + Account Info
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="xl:col-span-1 space-y-4">

            {{-- Avatar card --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 text-center">
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-3xl font-bold mx-auto ring-4 ring-primary-100 dark:ring-primary-900/30">
                    {{ strtoupper(substr($user->f_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->l_name ?? '', 0, 1)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>

                <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                    @if($user->is_active)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Active
                        </span>
                    @endif
                    @if($two_factor_enabled)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            2FA On
                        </span>
                    @endif
                </div>
            </div>

            {{-- Account info --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 divide-y divide-gray-100 dark:divide-gray-800">
                @php $role = $user->roles->first(); @endphp
                <div class="px-4 py-3 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Company</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $company->name ?? '—' }}</span>
                </div>
                <div class="px-4 py-3 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Role</span>
                    <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ $role->name ?? '—' }}</span>
                </div>
                <div class="px-4 py-3 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Member since</span>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="px-4 py-3 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Last login</span>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             RIGHT COLUMN — Forms
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- ─── Profile Info ───────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Profile Information</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Update your name, contact details and notification preferences</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name <span class="text-red-500">*</span></label>
                            <input wire:model.live.debounce.300ms="f_name" type="text" class="w-full px-3 py-2 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                            @error('f_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input wire:model.live.debounce.300ms="l_name" type="text" class="w-full px-3 py-2 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                            @error('l_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                        <input type="email" value="{{ $email }}" disabled class="w-full px-3 py-2 text-sm border rounded-lg bg-gray-100 dark:bg-gray-700 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-75">
                        <p class="mt-1 text-xs text-gray-500">Email address cannot be changed.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                        <input wire:model.live.debounce.300ms="phone" type="tel" placeholder="+1 (555) 000-0000" class="w-full px-3 py-2 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                        @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative" x-data>
                            <input wire:model="email_notifications" type="checkbox" class="sr-only peer" id="email-notif">
                            <div class="w-10 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer-checked:bg-primary-600 transition-colors cursor-pointer" for="email-notif"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4 pointer-events-none"></div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Email Notifications</span>
                            <p class="text-xs text-gray-500">Receive updates about orders and manifests via email</p>
                        </div>
                    </label>
                    <div class="flex justify-end pt-2">
                        <button wire:click="saveProfile" wire:loading.attr="disabled" class="flex items-center gap-2 px-5 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                            <span wire:loading.remove wire:target="saveProfile">Save Profile</span>
                            <span wire:loading wire:target="saveProfile" class="flex items-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Saving…</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ─── Update Password ─────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Update Password</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Must be at least 8 characters with uppercase, number and symbol</p>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Current password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input wire:model="current_password" type="{{ $show_current ? 'text' : 'password' }}" class="w-full px-3 py-2 pr-10 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                            <button type="button" wire:click="$toggle('show_current')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                @if($show_current)<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @else<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>@endif
                            </button>
                        </div>
                        @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- New password + strength --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password <span class="text-red-500">*</span></label>
                            <button type="button" wire:click="generatePassword" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Generate
                            </button>
                        </div>
                        <div class="relative">
                            <input wire:model.live="new_password" type="{{ $show_new ? 'text' : 'password' }}" class="w-full px-3 py-2 pr-10 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                            <button type="button" wire:click="$toggle('show_new')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                @if($show_new)<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @else<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>@endif
                            </button>
                        </div>

                        {{-- Strength bar --}}
                        @if(strlen($new_password) > 0)
                        <div class="mt-2 space-y-2">
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                <div class="h-1.5 flex-1 rounded-full transition-colors duration-300 {{ $password_strength >= $i
                                    ? ($password_strength <= 2 ? 'bg-red-500' : ($password_strength <= 3 ? 'bg-yellow-500' : 'bg-primary-500'))
                                    : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                @endfor
                            </div>
                            <p class="text-xs {{ $password_strength <= 2 ? 'text-red-500' : ($password_strength <= 3 ? 'text-yellow-600' : 'text-primary-600') }}">
                                {{ ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'][$password_strength] }}
                            </p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                @foreach(['length' => '8+ characters', 'uppercase' => 'Uppercase letter', 'lowercase' => 'Lowercase letter', 'number' => 'Number', 'special' => 'Special character'] as $key => $label)
                                <div class="flex items-center gap-1.5 text-xs {{ $password_checks[$key] ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}">
                                    @if($password_checks[$key])
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                                    @endif
                                    {{ $label }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @error('new_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Confirm password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input wire:model.live="new_password_confirmation" type="{{ $show_confirm ? 'text' : 'password' }}" class="w-full px-3 py-2 pr-10 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors
                                {{ strlen($new_password_confirmation) > 0 ? ($new_password === $new_password_confirmation ? 'border-primary-500' : 'border-red-400') : '' }}">
                            <button type="button" wire:click="$toggle('show_confirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                @if($show_confirm)<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @else<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>@endif
                            </button>
                        </div>
                        @if(strlen($new_password_confirmation) > 0 && $new_password !== $new_password_confirmation)
                            <p class="mt-1 text-xs text-red-500">Passwords do not match</p>
                        @endif
                    </div>

                    <div class="flex justify-end pt-2">
                        <button wire:click="updatePassword" wire:loading.attr="disabled" class="flex items-center gap-2 px-5 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                            <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                            <span wire:loading wire:target="updatePassword" class="flex items-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Updating…</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ─── Two-Factor Authentication ───────────────────────────── --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Add an extra layer of security using an authenticator app</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $two_factor_enabled ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                        {{ $two_factor_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                <div class="p-6">
                    @if(!$two_factor_enabled && !$show_2fa_setup)
                        {{-- Enable prompt --}}
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300">When 2FA is enabled, you'll be prompted for a 6-digit code from your authenticator app every time you log in.</p>
                                <p class="text-xs text-gray-500 mt-1">Works with Google Authenticator, Authy, 1Password, and other TOTP apps.</p>
                                <button wire:click="initTwoFactor" wire:loading.attr="disabled" class="mt-4 flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Enable 2FA
                                </button>
                            </div>
                        </div>

                    @elseif($show_2fa_setup)
                        {{-- QR code setup --}}
                        <div class="space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                    <img src="data:image/svg+xml;base64,{{ $qrCodeSvg }}" alt="2FA QR Code" class="w-36 h-36">
                                </div>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <p class="font-medium text-gray-900 dark:text-white">Scan this QR code</p>
                                    <ol class="list-decimal list-inside space-y-1 text-xs text-gray-500">
                                        <li>Open your authenticator app (Google Authenticator, Authy…)</li>
                                        <li>Tap <strong>+</strong> and choose "Scan QR Code"</li>
                                        <li>Scan the code on the left</li>
                                        <li>Enter the 6-digit code below to confirm</li>
                                    </ol>
                                    <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs font-mono break-all text-gray-500">
                                        {{ $pending_secret }}
                                    </div>
                                    <p class="text-xs text-gray-400">Can't scan? Enter this key manually in your app.</p>
                                </div>
                            </div>
                            <form wire:submit="confirmTwoFactor" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verification Code</label>
                                    <input wire:model="two_factor_code" type="text" inputmode="numeric" maxlength="6" placeholder="000000" class="w-40 px-3 py-2 text-center text-lg tracking-widest font-mono border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                    @error('two_factor_code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="confirmTwoFactor" class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                                        <span wire:loading.remove wire:target="confirmTwoFactor">Confirm &amp; Enable</span>
                                        <span wire:loading wire:target="confirmTwoFactor" class="flex items-center gap-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                            Verifying…
                                        </span>
                                    </button>
                                    <button type="button" wire:click="cancelTwoFactor" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                                </div>
                            </form>
                        </div>

                    @else
                        {{-- 2FA is enabled --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                                <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <p class="text-sm text-primary-700 dark:text-primary-300">Two-factor authentication is active. Your account has an extra layer of security.</p>
                            </div>

                            {{-- Recovery codes --}}
                            @if($show_recovery_codes && count($recovery_codes) > 0)
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <div class="flex items-start gap-2 mb-3">
                                    <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                    <div>
                                        <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Save your recovery codes</p>
                                        <p class="text-xs text-yellow-700 dark:text-yellow-400">Store these somewhere safe. Each code can only be used once if you lose access to your authenticator.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                    @foreach($recovery_codes as $code)
                                    <div class="px-3 py-1.5 bg-white dark:bg-gray-900 border border-yellow-200 dark:border-yellow-700 rounded text-center text-gray-800 dark:text-gray-200">{{ $code }}</div>
                                    @endforeach
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ implode('\n', $recovery_codes) }}')" class="mt-3 text-xs text-yellow-700 dark:text-yellow-400 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Copy all codes
                                </button>
                            </div>
                            @endif

                            <div class="flex flex-wrap gap-3">
                                <button wire:click="viewRecoveryCodes" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">View Recovery Codes</button>
                                <button wire:click="$set('show_disable_confirm', true)" class="px-4 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 text-red-600 dark:text-red-400 text-sm font-medium rounded-lg border border-red-200 dark:border-red-800 transition-colors">Disable 2FA</button>
                            </div>

                            @if($show_disable_confirm)
                            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg space-y-3">
                                <p class="text-sm text-red-700 dark:text-red-400 font-medium">Confirm your password to disable 2FA</p>
                                <input wire:model="disable_password" type="password" placeholder="Your current password" class="w-full px-3 py-2 text-sm border rounded-lg bg-white dark:bg-gray-900 border-red-200 dark:border-red-700 text-gray-900 dark:text-white focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                @error('disable_password')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                                <div class="flex gap-3">
                                    <button wire:click="disableTwoFactor" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">Confirm Disable</button>
                                    <button wire:click="$set('show_disable_confirm', false)" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>{{-- /right column --}}
    </div>{{-- /grid --}}
</div>
