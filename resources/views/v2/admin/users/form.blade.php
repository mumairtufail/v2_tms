@extends('v2.layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<div class="space-y-6">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => isset($user) ? 'Edit User' : 'Create User']
    ]" />

    <!-- 2. Page Header with back button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($user) ? 'Edit User' : 'Create User'" 
            :description="isset($user) ? 'Update user information for ' . $user->name : 'Add a new user to the system'" 
        />
    </div>

    <!-- 3. Form Container -->
    <x-table-container>
        <form 
            action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" 
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="p-6 space-y-8">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <x-input-label for="f_name" value="First Name" :required="true" />
                            <x-text-input 
                                id="f_name" 
                                name="f_name" 
                                type="text"
                                :value="old('f_name', $user->f_name ?? '')"
                                required
                                placeholder="John"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('f_name')" class="mt-2" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <x-input-label for="l_name" value="Last Name" :required="true" />
                            <x-text-input 
                                id="l_name" 
                                name="l_name" 
                                type="text"
                                :value="old('l_name', $user->l_name ?? '')"
                                required
                                placeholder="Doe"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('l_name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <x-input-label for="email" value="Email Address" :required="!isset($user)" />
                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                :value="old('email', $user->email ?? '')"
                                :required="!isset($user)"
                                :disabled="isset($user)"
                                placeholder="john@example.com"
                                class="mt-1 w-full"
                            />
                            @if(isset($user))
                                <p class="mt-1 text-xs text-gray-500">Email address cannot be changed.</p>
                            @else
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            @endif
                        </div>

                        <!-- Phone -->
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input 
                                id="phone" 
                                name="phone" 
                                type="tel"
                                :value="old('phone', $user->phone ?? '')"
                                placeholder="+1 (555) 000-0000"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6"
                     x-data="{
                         password: '{{ old('password', '') }}',
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
                         }
                     }">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Password
                        @if(isset($user))
                        <span class="text-sm font-normal text-gray-500">(Leave blank to keep current)</span>
                        @endif
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password field with strength -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <x-input-label for="password" :value="isset($user) ? 'New Password' : 'Password'" :required="!isset($user)" />
                                <button type="button" @click="generatePassword()" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Generate
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                       name="password" id="password"
                                       x-model="password"
                                       {{ !isset($user) ? 'required' : '' }}
                                       placeholder="••••••••"
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
                            <x-input-label for="password_confirmation" value="Confirm Password" :required="!isset($user)" />
                            <div class="relative mt-1">
                                <input :type="showConfirmation ? 'text' : 'password'"
                                       name="password_confirmation" id="password_confirmation"
                                       x-model="passwordConfirmation"
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

                <!-- Company & Status Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Company Assignment
                    </h3>

                    @php
                        $defaultRoleId = isset($user) && $user->roles->first()
                            ? $user->roles->first()->id
                            : ($roles->firstWhere('name', 'Admin')?->id
                                ?? $roles->first(fn($r) => stripos($r->name, 'admin') !== false)?->id
                                ?? '');
                    @endphp
                    <input type="hidden" name="role" value="{{ old('role', $defaultRoleId) }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <x-input-label for="company_id" value="Company" :required="true" />
                                <a href="{{ route('admin.companies.create') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Company
                                </a>
                            </div>
                            @if($companies->isEmpty())
                                <div class="flex items-center gap-2 px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                                    <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-yellow-700 dark:text-yellow-300">No companies yet — add one first.</span>
                                </div>
                                <input type="hidden" name="company_id" value="">
                            @else
                                <select
                                    id="company_id"
                                    name="company_id"
                                    required
                                    class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                >
                                    <option value="">Select a company</option>
                                    @foreach($companies as $id => $name)
                                    <option value="{{ $id }}" {{ old('company_id', $user->company_id ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
                            @endif
                        </div>

                        <!-- Status -->
                        <div x-data="{ statusActive: {{ old('status', $user->status ?? 'active') === 'active' ? 'true' : 'false' }} }">
                            <x-input-label value="Status" :required="true" />
                            <input type="hidden" name="status" :value="statusActive ? 'active' : 'inactive'">
                            <div class="mt-1">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <button 
                                        type="button"
                                        @click="statusActive = !statusActive"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                        :class="statusActive ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    >
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform" :class="statusActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="statusActive ? 'Active' : 'Inactive'"></p>
                                        <p class="text-sm text-gray-500" x-text="statusActive ? 'User can log in' : 'User cannot log in'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
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
                    {{ isset($user) ? 'Update User' : 'Create User' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
