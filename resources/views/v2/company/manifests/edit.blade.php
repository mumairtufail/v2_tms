@extends('v2.layouts.app')

@section('title', 'Edit Manifest - ' . $manifest->code)

@push('styles')
<style>
    /* Stop Card Animations */
    .stop-card { transition: all 0.3s ease; }
    .stop-card:hover { transform: translateY(-2px); }
    .stop-form-expanded { animation: slideDown 0.3s ease; }
    @keyframes slideDown {
        from { opacity: 0; max-height: 0; }
        to { opacity: 1; max-height: 2000px; }
    }
    
    /* Modal Animations */
    .v2-modal-backdrop {
        position: fixed; 
        inset: 0; 
        background: rgba(0,0,0,0.5);
        z-index: 9999; 
        opacity: 0; 
        visibility: hidden;
        transition: all 0.3s ease;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .v2-modal-backdrop.show { 
        opacity: 1; 
        visibility: visible; 
    }
    .v2-modal-content {
        transform: translateY(-20px) scale(0.95);
        transition: all 0.3s ease;
        position: relative;
        margin: 2rem auto;
    }
    .v2-modal-backdrop.show .v2-modal-content {
        transform: translateY(0) scale(1);
    }
    
    /* Selection Items */
    .selection-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .selection-item:hover {
        transform: translateX(4px);
        border-color: rgb(var(--primary-500));
    }
    .selection-item.selected {
        background: rgb(var(--primary-50));
        border-color: rgb(var(--primary-500));
    }
    .dark .selection-item.selected {
        background: rgba(var(--primary-500), 0.1);
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="manifestEdit()" @save-resource.window="handleResourceSave($event.detail.type)">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Manifests', 'url' => route('v2.manifests.index', ['company' => $company->slug])],
        ['label' => $manifest->code]
    ]" />

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('v2.manifests.index', ['company' => $company->slug]) }}" 
               class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $manifest->code }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manifest Management</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-full text-sm font-medium 
                {{ $manifest->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                   ($manifest->status === 'dispatched' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                {{ ucfirst($manifest->status ?? 'pending') }}
            </span>
            <form action="{{ route('v2.manifests.destroy', ['company' => $company->slug, 'manifest' => $manifest->id]) }}" 
                  method="POST" 
                  x-data="{ deleting: false }"
                  @submit="return confirm('Are you sure you want to delete this manifest?') ? (deleting = true) : false">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        :disabled="deleting"
                        class="px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50">
                    <svg x-show="!deleting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <svg x-show="deleting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="deleting ? 'Deleting...' : 'Delete'"></span>
                </button>
            </form>
        </div>
    </div>

    <!-- Action Buttons Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button @click="openResourceModal('driver')" 
                class="group p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
            <div x-show="loadingDriver" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] flex items-center justify-center z-10">
                <svg class="animate-spin h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white">Add Driver</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $manifest->drivers->count() }} assigned</p>
            </div>
        </button>

        <button @click="openResourceModal('equipment')" 
                class="group p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-accent-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
            <div x-show="loadingEquipment" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] flex items-center justify-center z-10">
                <svg class="animate-spin h-6 w-6 text-accent-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-accent-100 dark:bg-accent-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white">Add Equipment</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $manifest->equipments->count() }} assigned</p>
            </div>
        </button>

        <button @click="openResourceModal('carrier')" 
                class="group p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
            <div x-show="loadingCarrier" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] flex items-center justify-center z-10">
                <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white">Add Carrier</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $manifest->carriers->count() }} assigned</p>
            </div>
        </button>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
            <button @click="activeTab = 'overview'" 
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'overview', 
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400': activeTab !== 'overview' }" 
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Overview
            </button>
            <button @click="activeTab = 'stops'" 
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'stops', 
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400': activeTab !== 'stops' }" 
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                Stops
            </button>
            <button @click="activeTab = 'documents'" 
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'documents', 
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400': activeTab !== 'documents' }" 
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Documents
            </button>
            <button @click="activeTab = 'financials'" 
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'financials', 
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400': activeTab !== 'financials' }" 
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Financials
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div class="space-y-6">
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <!-- Map Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="h-80 bg-gray-100 dark:bg-gray-900">
                    <iframe class="w-full h-full border-0" 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d46830151.11795828!2d-119.8093025!3d44.24236485!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54eab584e432360b%3A0x1c3bb99243deb742!2sUnited%20States!5e0!3m2!1sen!2s!4v1738424902047!5m2!1sen!2s">
                    </iframe>
                </div>
            </div>

            <!-- Manifest Details Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manifest Details</h3>
                </div>
                <form action="{{ route('v2.manifests.update', ['company' => $company->slug, 'manifest' => $manifest->id]) }}" 
                      method="POST" class="p-6" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manifest Code</label>
                            <input type="text" value="{{ $manifest->code }}" readonly 
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 bg-gray-50 cursor-not-allowed">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                                <option value="pending" {{ $manifest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="dispatched" {{ $manifest->status == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="in_transit" {{ $manifest->status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="completed" {{ $manifest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $manifest->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ $manifest->start_date }}" 
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Freight Description</label>
                            <input type="text" name="freight" value="{{ $manifest->freight }}" 
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" :disabled="submitting" 
                                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Saving...' : 'Save Changes'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stops Tab -->
        <div x-show="activeTab === 'stops'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
            @include('v2.company.manifests.partials.stops-tab')
        </div>

        <!-- Documents Tab -->
        <div x-show="activeTab === 'documents'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
            @include('v2.company.manifests.partials.documents-tab')
        </div>

        <!-- Financials Tab -->
        <div x-show="activeTab === 'financials'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
            @include('v2.company.manifests.partials.financials-tab')
        </div>
    </div>

    <!-- Driver Selection Modal -->
    <x-resource-assignment-modal 
        type="driver"
        title="Assign Drivers"
        description="Select drivers to add to this manifest"
        icon="users"
        color="primary"
        empty-message="No Drivers Found"
    />

    <!-- Equipment Selection Modal -->
    <x-resource-assignment-modal 
        type="equipment"
        title="Assign Equipment"
        description="Select equipment to add to this manifest"
        icon="truck"
        color="accent"
        empty-message="No Equipment Found"
    />

    <!-- Carrier Selection Modal -->
    <x-resource-assignment-modal 
        type="carrier"
        title="Assign Carriers"
        description="Select carriers to add to this manifest"
        icon="building"
        color="blue"
        empty-message="No Carriers Found"
    />

    <!-- Add Stop Modal -->
    @include('v2.company.manifests.partials.stop-modal')

    <!-- Rate Confirmation Modal -->
    @include('v2.company.manifests.partials.rate-confirmation-modal')
</div>

@push('scripts')
<script>
function manifestEdit() {
    return {
        activeTab: localStorage.getItem('manifestActiveTab') || 'overview',
        manifestId: @json($manifest->id),
        companySlug: @json($company->slug),
        
        // Modal states
        showDriverModal: false,
        showEquipmentModal: false,
        showCarrierModal: false,
        showStopModal: false,
        showRateConfirmationModal: false,

        // Loading states
        loadingDriver: false,
        loadingEquipment: false,
        loadingCarrier: false,
        savingResource: false,

        // Resource data
        drivers: [],
        equipment: [],
        carriers: [],
        
        // Selections
        selectedDrivers: new Set(),
        selectedEquipment: new Set(),
        selectedCarriers: new Set(),
        
        // Search strings
        driverSearch: '',
        equipmentSearch: '',
        carrierSearch: '',

        init() {
            this.$watch('activeTab', value => localStorage.setItem('manifestActiveTab', value));
        },

        async openResourceModal(type) {
            const typeUpper = type.charAt(0).toUpperCase() + type.slice(1);
            this[`show${typeUpper}Modal`] = true;
            this[`loading${typeUpper}`] = true;
            
            try {
                // Determine the correct plural form for API/state
                const plural = type === 'equipment' ? 'equipment' : type + 's';
                const endpoint = `/${this.companySlug}/manifests/${this.manifestId}/${plural}/available`;
                
                const response = await fetch(endpoint);
                const data = await response.json();
                
                if (data.success) {
                    this[plural] = data[plural];
                    // Pre-select already assigned items
                    this[`selected${typeUpper}${type === 'equipment' ? '' : 's'}`] = new Set(data.assigned.map(i => i.id));
                }
            } catch (error) {
                console.error(`Error loading ${type}s:`, error);
            } finally {
                this[`loading${typeUpper}`] = false;
            }
            
            document.body.style.overflow = 'hidden';
        },

        closeDriverModal() { this.showDriverModal = false; document.body.style.overflow = ''; },
        closeEquipmentModal() { this.showEquipmentModal = false; document.body.style.overflow = ''; },
        closeCarrierModal() { this.showCarrierModal = false; document.body.style.overflow = ''; },
        closeStopModal() { this.showStopModal = false; document.body.style.overflow = ''; },

        toggleDriver(id) {
            this.selectedDrivers = new Set(this.selectedDrivers);
            this.selectedDrivers.has(id) ? this.selectedDrivers.delete(id) : this.selectedDrivers.add(id);
        },

        toggleEquipment(id) {
            this.selectedEquipment = new Set(this.selectedEquipment);
            this.selectedEquipment.has(id) ? this.selectedEquipment.delete(id) : this.selectedEquipment.add(id);
        },

        toggleCarrier(id) {
            this.selectedCarriers = new Set(this.selectedCarriers);
            this.selectedCarriers.has(id) ? this.selectedCarriers.delete(id) : this.selectedCarriers.add(id);
        },

        get filteredDrivers() {
            return this.drivers.filter(d => 
                (d.name || '').toLowerCase().includes(this.driverSearch.toLowerCase()) ||
                (d.email || '').toLowerCase().includes(this.driverSearch.toLowerCase())
            );
        },

        get filteredEquipments() {
            return this.equipment.filter(e => 
                (e.name || e.unit_number || '').toLowerCase().includes(this.equipmentSearch.toLowerCase()) ||
                (e.type || '').toLowerCase().includes(this.equipmentSearch.toLowerCase())
            );
        },

        get filteredCarriers() {
            return this.carriers.filter(c => 
                (c.carrier_name || '').toLowerCase().includes(this.carrierSearch.toLowerCase()) ||
                (c.dot_id || '').toString().includes(this.carrierSearch.toLowerCase())
            );
        },

        async handleResourceSave(type) {
            const typeUpper = type.charAt(0).toUpperCase() + type.slice(1);
            const plural = type === 'equipment' ? 'equipment' : type + 's';
            const selectedVar = `selected${typeUpper}${type === 'equipment' ? '' : 's'}`;
            
            this.savingResource = true;
            try {
                const response = await fetch(`/${this.companySlug}/manifests/${this.manifestId}/${plural}/sync`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ [`${type}_ids`]: Array.from(this[selectedVar]) })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                console.error(`Error saving ${type}s:`, error);
            } finally {
                this.savingResource = false;
            }
        },

        openStopModal() {
            this.showStopModal = true;
            document.body.style.overflow = 'hidden';
        },

        openRateConfirmationModal() {
            this.showRateConfirmationModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeRateConfirmationModal() {
            this.showRateConfirmationModal = false;
            document.body.style.overflow = '';
        }
    }
}

function costEstimates(initialData) {
    // Robustly handle initialData as array or object
    let data = [];
    if (Array.isArray(initialData)) {
        data = initialData;
    } else if (initialData && typeof initialData === 'object') {
        data = Object.values(initialData);
    }
    
    console.log('Initializing costEstimates with data:', data);

    return {
        rows: data.length > 0 ? data : [
            { type: 'fuel', description: '', qty: 1, rate: 0 },
        ],
        submitting: false,
        
        get total() {
            return this.rows.reduce((sum, row) => sum + (Number(row.qty || 0) * Number(row.rate || 0)), 0);
        },
        
        addRow() {
            console.log('Adding new row');
            this.rows.push({ type: 'miscellaneous', description: '', qty: 1, rate: 0 });
        },
        
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            }).format(value);
        }
    }
}
</script>
@endpush
@endsection
