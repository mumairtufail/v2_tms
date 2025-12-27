@extends('v2.layouts.app')

@section('title', isset($equipment) ? 'Edit Equipment' : 'Create Equipment')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Equipment', 'url' => route('v2.equipment.index', ['company' => $company->slug])],
        ['label' => isset($equipment) ? 'Edit Equipment' : 'Create Equipment']
    ]" />

    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('v2.equipment.index', ['company' => $company->slug]) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($equipment) ? 'Edit Equipment' : 'Create Equipment'" 
            :description="isset($equipment) ? 'Update equipment information' : 'Add new equipment'" 
        />
    </div>

    <!-- Form -->
    <x-table-container>
        <form 
            action="{{ isset($equipment) ? route('v2.equipment.update', ['company' => $company->slug, 'equipment' => $equipment->id]) : route('v2.equipment.store', ['company' => $company->slug]) }}" 
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($equipment)) @method('PUT') @endif

            <div class="p-6 space-y-8">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                        Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-text-input
                            label="Equipment Name/ID"
                            name="name"
                            :value="old('name', $equipment->name ?? '')"
                            placeholder="e.g. TRUCK-001"
                            required
                        />
                        <x-select-input
                            label="Type"
                            name="type"
                            :value="old('type', $equipment->type ?? '')"
                            :options="['Truck' => 'Truck', 'Trailer' => 'Trailer', 'Van' => 'Van', 'Other' => 'Other']"
                            required
                        />
                        <x-text-input
                            label="Sub Type"
                            name="sub_type"
                            :value="old('sub_type', $equipment->sub_type ?? '')"
                            placeholder="e.g. Reefer, Flatbed"
                        />
                        <x-select-input
                            label="Status"
                            name="status"
                            :value="old('status', $equipment->status ?? 'Available')"
                            :options="['Available' => 'Available', 'In Use' => 'In Use', 'Maintenance' => 'Maintenance']"
                            required
                        />
                    </div>
                </div>

                <!-- Details -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Details
                    </h3>
                    <div class="grid grid-cols-1 gap-6">
                        <x-text-input
                            label="Description"
                            name="desc"
                            :value="old('desc', $equipment->desc ?? '')"
                            placeholder="Additional details about the equipment"
                        />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-text-input
                                label="Last Location"
                                name="last_location"
                                :value="old('last_location', $equipment->last_location ?? '')"
                                placeholder="City, State"
                            />
                            <x-text-input
                                label="Last Seen"
                                name="last_seen"
                                type="datetime-local"
                                :value="old('last_seen', isset($equipment->last_seen) ? \Carbon\Carbon::parse($equipment->last_seen)->format('Y-m-d\TH:i') : '')"
                            />
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
                    {{ isset($equipment) ? 'Update Equipment' : 'Create Equipment' }}
                </button>
                <a href="{{ route('v2.equipment.index', ['company' => $company->slug]) }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
