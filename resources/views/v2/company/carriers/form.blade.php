@extends('v2.layouts.app')

@section('title', isset($carrier) ? 'Edit Carrier' : 'Create Carrier')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Carriers', 'url' => route('v2.carriers.index', ['company' => $company->slug])],
        ['label' => isset($carrier) ? 'Edit Carrier' : 'Create Carrier']
    ]" />

    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('v2.carriers.index', ['company' => $company->slug]) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($carrier) ? 'Edit Carrier' : 'Create Carrier'" 
            :description="isset($carrier) ? 'Update carrier information' : 'Add a new carrier'" 
        />
    </div>

    <!-- Form -->
    <x-table-container>
        <form 
            action="{{ isset($carrier) ? route('v2.carriers.update', ['company' => $company->slug, 'carrier' => $carrier->id]) : route('v2.carriers.store', ['company' => $company->slug]) }}" 
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($carrier)) @method('PUT') @endif

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
                            label="Carrier Name"
                            name="carrier_name"
                            :value="old('carrier_name', $carrier->carrier_name ?? '')"
                            placeholder="Enter carrier name"
                            required
                        />
                        <x-text-input
                            label="DOT ID"
                            name="dot_id"
                            :value="old('dot_id', $carrier->dot_id ?? '')"
                            placeholder="e.g. 1234567"
                        />
                        <x-text-input
                            label="Docket Number (MC)"
                            name="docket_number"
                            :value="old('docket_number', $carrier->docket_number ?? '')"
                            placeholder="e.g. MC123456"
                        />
                        <x-text-input
                            label="Currency"
                            name="currency"
                            :value="old('currency', $carrier->currency ?? 'USD')"
                            placeholder="e.g. USD"
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
                                name="address_1"
                                :value="old('address_1', $carrier->address_1 ?? '')"
                                placeholder="Street address"
                            />
                        </div>
                        <x-text-input
                            label="City"
                            name="city"
                            :value="old('city', $carrier->city ?? '')"
                            placeholder="City"
                        />
                        <x-text-input
                            label="State/Province"
                            name="state"
                            :value="old('state', $carrier->state ?? '')"
                            placeholder="State"
                        />
                        <x-text-input
                            label="Postal Code"
                            name="post_code"
                            :value="old('post_code', $carrier->post_code ?? '')"
                            placeholder="ZIP/Postal Code"
                        />
                        <x-text-input
                            label="Country"
                            name="country"
                            :value="old('country', $carrier->country ?? '')"
                            placeholder="Country"
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
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Status</span>
                                <input type="hidden" name="is_active" value="0">
                                <x-toggle-input
                                    name="is_active"
                                    value="1"
                                    :checked="old('is_active', $carrier->is_active ?? true)"
                                />
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
                    {{ isset($carrier) ? 'Update Carrier' : 'Create Carrier' }}
                </button>
                <a href="{{ route('v2.carriers.index', ['company' => $company->slug]) }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
