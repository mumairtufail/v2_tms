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
                            <x-input-label for="email" value="Email Address" :required="true" />
                            <x-text-input 
                                id="email" 
                                name="email" 
                                type="email"
                                :value="old('email', $user->email ?? '')"
                                required
                                placeholder="john@example.com"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
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
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
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
                        <div>
                            <x-input-label for="password" value="Password" :required="!isset($user)" />
                            <x-text-input 
                                id="password" 
                                name="password" 
                                type="password"
                                :required="!isset($user)"
                                placeholder="••••••••"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" value="Confirm Password" :required="!isset($user)" />
                            <x-text-input 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                type="password"
                                placeholder="••••••••"
                                class="mt-1 w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- Company & Role Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Company & Role Assignment
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Company -->
                        <div>
                            <x-input-label for="company_id" value="Company" :required="true" />
                            <select 
                                id="company_id" 
                                name="company_id"
                                required
                                class="mt-1 w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                                <option value="">Select a company</option>
                                @foreach($companies as $id => $name)
                                <option value="{{ $id }}" {{ old('company_id', $user->company_id ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div>
                            <x-input-label for="role" value="Role" :required="true" />
                            <select 
                                id="role" 
                                name="role"
                                required
                                class="mt-1 w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                                <option value="">Select a role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role', isset($user) && $user->roles->first() ? $user->roles->first()->id : '') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
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
