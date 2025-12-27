@extends('v2.layouts.app')

@section('title', isset($company) ? 'Edit Company' : 'Create Company')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Companies', 'url' => route('admin.companies.index')],
        ['label' => isset($company) ? 'Edit Company' : 'Create Company']
    ]" />

    <!-- Page Header with back button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.companies.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($company) ? 'Edit Company' : 'Create Company'" 
            :description="isset($company) ? 'Update information for ' . $company->name : 'Add a new company to the system'" 
        />
    </div>

    <!-- Form Container -->
    <x-table-container>
        <form 
            action="{{ isset($company) ? route('admin.companies.update', $company) : route('admin.companies.store') }}" 
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($company))
                @method('PUT')
            @endif

            <div class="p-6 space-y-8">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Company Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company Name -->
                        <div>
                            <x-input-label for="name" value="Company Name" :required="true" />
                            <x-text-input 
                                id="name" 
                                name="name" 
                                type="text"
                                :value="old('name', $company->name ?? '')"
                                required
                                placeholder="Enter company name"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input 
                                id="phone" 
                                name="phone" 
                                type="text"
                                :value="old('phone', $company->phone ?? '')"
                                placeholder="+1-000-000-0000"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Address
                    </h3>
                    <div>
                        <textarea 
                            id="address" 
                            name="address" 
                            rows="3" 
                            placeholder="Enter company address"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        >{{ old('address', $company->address ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>
                </div>

                <!-- Settings Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </h3>

                    @if(isset($company))
                    <div class="mb-6">
                        <x-input-label value="Current Slug" />
                        <div class="mt-1">
                            <code class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-600 dark:text-gray-400 text-sm inline-block">{{ $company->slug }}</code>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">The slug will be automatically updated based on the company name.</p>
                    </div>
                    @endif

                    <!-- Status Toggle -->
                    <div x-data="{ active: {{ old('is_active', $company->is_active ?? true) ? 'true' : 'false' }} }">
                        <input type="hidden" name="is_active" :value="active ? '1' : '0'">
                        <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <button 
                                type="button"
                                @click="active = !active"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                :class="active ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform" :class="active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="active ? 'Active' : 'Inactive'"></p>
                                <p class="text-sm text-gray-500" x-text="active ? 'Company is visible and operational' : 'Company is hidden from users'"></p>
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
                    {{ isset($company) ? 'Update Company' : 'Create Company' }}
                </button>
                <a href="{{ route('admin.companies.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
