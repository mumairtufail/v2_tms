@extends('v2.layouts.app')

@section('title', isset($customer) ? 'Edit Customer' : 'Create Customer')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Customers', 'url' => route('v2.customers.index', ['company' => $company->slug])],
        ['label' => isset($customer) ? 'Edit Customer' : 'Create Customer']
    ]" />

    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('v2.customers.index', ['company' => $company->slug]) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($customer) ? 'Edit Customer' : 'Create Customer'" 
            :description="isset($customer) ? 'Update customer information' : 'Add a new customer'" 
        />
    </div>

    <!-- Form -->
    <x-table-container>
        <form 
            action="{{ isset($customer) ? route('v2.customers.update', ['company' => $company->slug, 'customer' => $customer->id]) : route('v2.customers.store', ['company' => $company->slug]) }}" 
            method="POST"
            x-data="{
                submitting: false,
                portal: {{ old('portal', $customer->portal ?? false) ? 'true' : 'false' }},
                shortcodeStatus: '{{ old('short_code', $customer->short_code ?? '') ? 'manual' : '' }}',
                nameDebounce: null,
                password: '',
                passwordConfirmation: '',
                showPassword: false,
                showConfirmation: false,
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
                },
                fetchShortcode(name) {
                    if (this.shortcodeStatus === 'manual') return;
                    clearTimeout(this.nameDebounce);
                    if (!name || name.trim().length < 1) return;
                    this.nameDebounce = setTimeout(() => {
                        fetch('{{ route('v2.customers.generate-short-code', ['company' => $company->slug]) }}?name=' + encodeURIComponent(name) + '&exclude_id={{ $customer->id ?? '' }}')
                            .then(r => r.json())
                            .then(data => {
                                if (data.short_code && this.shortcodeStatus !== 'manual') {
                                    document.getElementById('short_code').value = data.short_code;
                                    this.shortcodeStatus = 'auto';
                                }
                            });
                    }, 400);
                }
            }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($customer)) @method('PUT') @endif

            <div class="p-6 space-y-8">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-text-input
                            label="Customer Name"
                            name="name"
                            :value="old('name', $customer->name ?? '')"
                            placeholder="Enter customer name"
                            required
                            maxlength="255"
                            @input="fetchShortcode($event.target.value)"
                        />
                        <div>
                            <div class="flex items-center justify-between">
                                <x-input-label for="short_code" value="Short Code" />
                                <span x-show="shortcodeStatus === 'auto'" style="display:none" class="text-xs text-primary-500 font-medium">Auto-generated</span>
                            </div>
                            <x-text-input
                                name="short_code"
                                :value="old('short_code', $customer->short_code ?? '')"
                                placeholder="Auto-generated from name"
                                maxlength="3"
                                pattern="[A-Za-z0-9]{1,3}"
                                title="Up to 3 alphanumeric characters"
                                style="text-transform:uppercase"
                                class="mt-1"
                                @input="shortcodeStatus = $event.target.value ? 'manual' : ''; $event.target.value = $event.target.value.toUpperCase().replace(/[^A-Z0-9]/g,'')"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Up to 3 alphanumeric characters. Auto-filled from customer name — must be unique.</p>
                        </div>
                        <x-text-input
                            label="Email Address"
                            name="customer_email"
                            type="email"
                            :value="old('customer_email', $customer->customer_email ?? '')"
                            placeholder="email@example.com"
                            maxlength="255"
                        />
                        <x-select-input
                            label="Customer Type"
                            name="customer_type"
                            :value="old('customer_type', $customer->customer_type ?? 'other')"
                            :options="[
                                'shipper' => 'Shipper',
                                'broker' => 'Broker',
                                'carrier' => 'Carrier',
                                'other' => 'Other'
                            ]"
                        />
                    </div>
                </div>

                <!-- Address Information -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Address Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <x-text-input
                                label="Address"
                                name="address"
                                :value="old('address', $customer->address ?? '')"
                                placeholder="Street address"
                                maxlength="255"
                            />
                        </div>
                        <x-text-input
                            label="City"
                            name="city"
                            :value="old('city', $customer->city ?? '')"
                            placeholder="City"
                            maxlength="100"
                        />
                        <x-text-input
                            label="State/Province"
                            name="state"
                            :value="old('state', $customer->state ?? '')"
                            placeholder="State"
                            maxlength="100"
                        />
                        <x-text-input
                            label="Postal Code"
                            name="postal_code"
                            :value="old('postal_code', $customer->postal_code ?? '')"
                            placeholder="ZIP/Postal Code"
                            maxlength="20"
                        />
                        <x-text-input
                            label="Country"
                            name="country"
                            :value="old('country', $customer->country ?? '')"
                            placeholder="Country"
                            maxlength="100"
                        />
                    </div>
                </div>

                <!-- Settings -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-select-input
                            label="Currency"
                            name="currency"
                            :value="old('currency', $customer->currency ?? 'USD')"
                            :options="[
                                'USD' => 'USD — US Dollar',
                                'CAD' => 'CAD — Canadian Dollar'
                            ]"
                        />
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Status</span>
                                <input type="hidden" name="is_active" value="0">
                                <x-toggle-input
                                    name="is_active"
                                    value="1"
                                    :checked="old('is_active', $customer->is_active ?? true)"
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Portal Access</span>
                                <input type="hidden" name="portal" value="0">
                                <x-toggle-input
                                    name="portal"
                                    value="1"
                                    :checked="old('portal', $customer->portal ?? false)"
                                    @change="portal = $event.target.checked"
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Quote Required</span>
                                <input type="hidden" name="quote_required" value="0">
                                <x-toggle-input
                                    name="quote_required"
                                    value="1"
                                    :checked="old('quote_required', $customer->quote_required ?? false)"
                                />
                            </div>
                        </div>

                        <div x-show="portal" x-transition class="md:col-span-2 space-y-4 pt-2 border-t border-gray-200 dark:border-gray-700" @if(!old('portal', $customer->portal ?? false)) style="display:none" @endif>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Portal login credentials — the customer signs in with the email address above.
                                @if(isset($customer) && $customer->password) Leave the password blank to keep the current one. @endif
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Password with visibility toggle + strength -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ isset($customer) && $customer->password ? 'New Password' : 'Portal Password' }}
                                            @if(!isset($customer) || !$customer->password)<span class="text-red-500 ml-0.5">*</span>@endif
                                        </label>
                                        <button type="button" @click="generatePassword()" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Generate
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <input :type="showPassword ? 'text' : 'password'"
                                               name="password" id="password"
                                               x-model="password"
                                               autocomplete="new-password"
                                               placeholder="{{ isset($customer) && $customer->password ? 'Leave blank to keep current' : '••••••••' }}"
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

                                <!-- Confirm password with live match check -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Confirm Password
                                        @if(!isset($customer) || !$customer->password)<span class="text-red-500 ml-0.5">*</span>@endif
                                    </label>
                                    <div class="relative">
                                        <input :type="showConfirmation ? 'text' : 'password'"
                                               name="password_confirmation" id="password_confirmation"
                                               x-model="passwordConfirmation"
                                               autocomplete="new-password"
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
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-4 rounded-b-xl border-t border-gray-200 dark:border-gray-700">
                <button 
                    type="submit" 
                    :disabled="submitting"
                    class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                >
                    <template x-if="submitting">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
                </button>
                <a href="{{ route('v2.customers.index', ['company' => $company->slug]) }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
