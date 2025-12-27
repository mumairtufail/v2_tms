@extends('v2.layouts.app')

@section('title', isset($company) ? 'Edit Company' : 'Create Company')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Companies', 'url' => route('admin.companies.index')],
        ['label' => isset($company) ? 'Edit' : 'Create']
    ]" />

    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.companies.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($company) ? 'Edit Company' : 'Create Company'" 
            :description="isset($company) ? 'Update information for ' . $company->name : 'Add a new company to the system'" 
        />
    </div>

    <!-- Form -->
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

            <div class="p-6 space-y-6">
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

                <!-- Address -->
                <div>
                    <x-input-label for="address" value="Address" />
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="3" 
                        placeholder="Enter company address"
                        class="mt-1 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                    >{{ old('address', $company->address ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <!-- Current Slug (Edit only) -->
                @if(isset($company))
                <div>
                    <x-input-label value="Current Slug" />
                    <div class="mt-1">
                        <code class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-600 dark:text-gray-400 text-sm">{{ $company->slug }}</code>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">The slug will be automatically updated based on the company name.</p>
                </div>
                @endif

                <!-- Active Status - Modern Toggle Switch -->
                <x-toggle-switch 
                    name="is_active"
                    label="Active Status"
                    description="Enable or disable this company"
                    :checked="old('is_active', $company->is_active ?? true)"
                />
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-4 rounded-b-xl">
                <button 
                    type="submit" 
                    :disabled="submitting"
                    class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                >
                    <x-loader x-show="submitting" size="sm" />
                    <span x-text="submitting ? 'Saving...' : '{{ isset($company) ? 'Update Company' : 'Create Company' }}'"></span>
                </button>
                <a href="{{ route('admin.companies.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
