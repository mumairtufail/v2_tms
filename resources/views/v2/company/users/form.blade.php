@extends('v2.layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<x-page-header 
    :title="isset($user) ? 'Edit User' : 'Create User'"
    :description="isset($user) ? 'Update user information and permissions' : 'Add a new user to your organization'"
    :breadcrumbs="[
        ['label' => 'Users', 'url' => route('v2.users.index', ['company' => app('current.company')])],
        ['label' => isset($user) ? 'Edit User' : 'Create User']
    ]"
/>

<form method="POST" 
      action="{{ isset($user) ? route('v2.users.update', ['company' => app('current.company'), 'user' => $user->id]) : route('v2.users.store', ['company' => app('current.company')]) }}"
      class="w-full"
      autocomplete="off"
      x-data="{ submitting: false }"
      @submit="submitting = true">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif


    <!-- Basic Information -->
    <x-form-section 
        title="Basic Information"
        description="Enter the user's personal details"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-text-input
                label="First Name"
                name="f_name"
                :value="old('f_name', $user->f_name ?? '')"
                placeholder="John"
                required
            />

            <x-text-input
                label="Last Name"
                name="l_name"
                :value="old('l_name', $user->l_name ?? '')"
                placeholder="Doe"
                required
            />

            <x-text-input
                label="Email Address"
                name="email"
                type="email"
                :value="old('email', $user->email ?? '')"
                placeholder="john@example.com"
                :required="!isset($user)"
                :disabled="isset($user)"
                autocomplete="email"
            />
            @if(isset($user))
                <p class="-mt-4 text-xs text-gray-500">Email address cannot be changed.</p>
            @endif

            <x-text-input
                label="Phone Number"
                name="phone"
                type="tel"
                :value="old('phone', $user->phone ?? '')"
                placeholder="+1 (555) 000-0000"
                autocomplete="tel"
            />
        </div>
    </x-form-section>

    <!-- Password -->
    <x-form-section
        :title="isset($user) ? 'Password' : 'Password'"
        :description="isset($user) ? 'Leave blank to keep current password' : 'Set the initial password for this user'"
        class="mt-6"
    >
        {{-- x-data lives on this plain div, not the <x-form-section> tag itself —
             Blade does not compile @directives (like @js()) inside a component
             tag's own attributes, only within its slot content. --}}
        <div x-data="{
            password: @js(old('password', '')),
            passwordConfirmation: @js(old('password_confirmation', '')),
            showPassword: false,
            showConfirmation: false,
            init() {
                // Guard against browser extensions (password managers) that
                // sometimes inject a stringified DOM element instead of a
                // real value into password fields it mis-targets. Some of
                // these write straight to the DOM .value without firing an
                // input/change event, so x-model alone won't see it — poll
                // the raw DOM value directly for a few seconds after mount.
                const isGarbage = (value) => typeof value === 'string' && /^\[object [A-Za-z]+Element\]$/.test(value);
                const sanitize = () => {
                    if (this.$refs.passwordInput && isGarbage(this.$refs.passwordInput.value)) {
                        this.$refs.passwordInput.value = '';
                        this.password = '';
                    }
                    if (this.$refs.confirmInput && isGarbage(this.$refs.confirmInput.value)) {
                        this.$refs.confirmInput.value = '';
                        this.passwordConfirmation = '';
                    }
                };
                this.$watch('password', (value) => { if (isGarbage(value)) this.password = ''; });
                this.$watch('passwordConfirmation', (value) => { if (isGarbage(value)) this.passwordConfirmation = ''; });
                this.$refs.passwordInput?.addEventListener('focus', sanitize);
                this.$refs.passwordInput?.addEventListener('blur', sanitize);
                this.$refs.confirmInput?.addEventListener('focus', sanitize);
                this.$refs.confirmInput?.addEventListener('blur', sanitize);
                sanitize();
                setInterval(sanitize, 250);
            },
            get strength() {
                let s = 0;
                if (this.password.length >= 8) s++;
                if (/[A-Z]/.test(this.password)) s++;
                if (/[a-z]/.test(this.password)) s++;
                if (/[0-9]/.test(this.password)) s++;
                if (/[\W_]/.test(this.password)) s++;
                return s;
            },
            get checks() {
                return {
                    length: this.password.length >= 8,
                    uppercase: /[A-Z]/.test(this.password),
                    lowercase: /[a-z]/.test(this.password),
                    number: /[0-9]/.test(this.password),
                    special: /[\W_]/.test(this.password),
                };
            },
            generatePassword() {
                const chars = 'abcdefghijklmnopqrstuvwxyz';
                const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const numbers = '0123456789';
                const special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
                let pass = chars[Math.floor(Math.random() * chars.length)]
                    + upper[Math.floor(Math.random() * upper.length)]
                    + numbers[Math.floor(Math.random() * numbers.length)]
                    + special[Math.floor(Math.random() * special.length)];
                const all = chars + upper + numbers + special;
                for (let i = 0; i < 8; i++) pass += all[Math.floor(Math.random() * all.length)];
                this.password = pass.split('').sort(() => Math.random() - 0.5).join('');
                this.passwordConfirmation = this.password;
                this.showPassword = true;
                this.showConfirmation = true;
            }
        }"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Password field with strength -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ isset($user) ? 'New Password' : 'Password' }}
                        @if(!isset($user))<span class="text-red-500 ml-0.5">*</span>@endif
                    </label>
                    <button type="button" @click="generatePassword()" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Generate
                    </button>
                </div>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'"
                           name="password" id="password"
                           x-ref="passwordInput"
                           x-model="password"
                           autocomplete="new-password"
                           data-lpignore="true" data-1p-ignore data-bwignore data-form-type="other"
                           {{ !isset($user) ? 'required' : '' }}
                           placeholder="{{ isset($user) ? 'Leave blank to keep current' : '••••••••' }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors pr-10">
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPassword" style="display:none" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                <div x-show="password.length > 0" class="mt-2 space-y-2">
                    <div class="flex gap-1">
                        <template x-for="i in [1,2,3,4,5]" :key="i">
                            <div class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                                 :class="strength >= i ? (strength <= 2 ? 'bg-red-500' : (strength <= 3 ? 'bg-yellow-500' : 'bg-primary-500')) : 'bg-gray-200 dark:bg-gray-700'"></div>
                        </template>
                    </div>
                    <p class="text-xs" :class="strength <= 2 ? 'text-red-500' : (strength <= 3 ? 'text-yellow-600' : 'text-primary-600')"
                       x-text="['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'][strength]"></p>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                        <template x-for="item in [{key:'length',label:'8+ characters'},{key:'uppercase',label:'Uppercase letter'},{key:'lowercase',label:'Lowercase letter'},{key:'number',label:'Number'},{key:'special',label:'Special character'}]" :key="item.key">
                            <div class="flex items-center gap-1.5 text-xs" :class="checks[item.key] ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'">
                                <svg x-show="checks[item.key]" class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="!checks[item.key]" class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Confirm Password
                    @if(!isset($user))<span class="text-red-500 ml-0.5">*</span>@endif
                </label>
                <div class="relative">
                    <input :type="showConfirmation ? 'text' : 'password'"
                           name="password_confirmation" id="password_confirmation"
                           x-ref="confirmInput"
                           x-model="passwordConfirmation"
                           autocomplete="new-password"
                           data-lpignore="true" data-1p-ignore data-bwignore data-form-type="other"
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors pr-10 border"
                           :class="passwordConfirmation.length > 0 ? (password === passwordConfirmation ? 'border-primary-500' : 'border-red-400') : 'border-gray-200 dark:border-gray-700'">
                    <button type="button" @click="showConfirmation = !showConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg x-show="!showConfirmation" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showConfirmation" style="display:none" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                <p x-show="passwordConfirmation.length > 0 && password !== passwordConfirmation" class="mt-1 text-xs text-red-500">Passwords do not match</p>
            </div>
        </div>
        </div>
    </x-form-section>

    <!-- Role Assignment -->
    <x-form-section 
        title="Role Assignment"
        description="Assign role and status"
        class="mt-6"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            @if(isset($user) && $user->id === auth()->id())
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <input type="hidden" name="role" value="{{ old('role', $user->roles->first()?->id) }}">
                    <input type="text" value="{{ auth()->user()->primaryRoleLabel() }}" disabled class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">You cannot change your own role.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <input type="hidden" name="status" value="{{ $user->status }}">
                    <input type="text" value="{{ ucfirst($user->status) }}" disabled class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">You cannot change your own status.</p>
                </div>
            @else
            <x-select-input
                label="Role"
                name="role"
                :value="old('role', isset($user) && $user->roles->first() ? $user->roles->first()->id : '')"
                :options="collect($roles ?? [])->pluck('name', 'id')->toArray()"
                placeholder="Select a role"
                required
            />

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <div class="mt-2">
                    <input type="hidden" name="status" value="inactive">
                    <x-toggle-input
                        label="Active"
                        name="status"
                        value="active"
                        :checked="old('status', $user->status ?? 'active') === 'active'"
                        description="User can log in"
                    />
                </div>
            </div>
            @endif
        </div>
    </x-form-section>

    <!-- Additional Settings (Commented out) -->
    {{--
    <x-form-section 
        title="Additional Settings"
        description="Configure user preferences"
        class="mt-6"
    >
        <div class="space-y-4">
            <x-toggle-input
                label="Email Notifications"
                name="email_notifications"
                :checked="$user->email_notifications ?? true"
                description="Receive email notifications for important updates"
            />

            <x-toggle-input
                label="Two-Factor Authentication"
                name="two_factor_enabled"
                :checked="$user->two_factor_enabled ?? false"
                description="Require 2FA for enhanced security"
            />
        </div>
    </x-form-section>
    --}}

    <!-- Actions -->
    <div class="mt-6 flex items-center gap-3">
        <button type="submit" 
                :disabled="submitting"
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <template x-if="submitting">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
            {{ isset($user) ? 'Update User' : 'Create User' }}
        </button>
        <a href="{{ route('v2.users.index', ['company' => app('current.company')]) }}"
           class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-medium rounded-lg transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
