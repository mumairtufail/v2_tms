<!-- Carrier Selection Modal -->
<div x-show="showCarrierModal" 
     class="v2-modal-backdrop"
     :class="{ 'show': showCarrierModal }"
     @click.self="closeCarrierModal()"
     @keydown.escape.window="closeCarrierModal()"
     style="display: none;">
    
    <div class="v2-modal-content bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden" style="max-height: calc(100vh - 4rem);">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Carriers</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select carriers to assign to this manifest</p>
            </div>
            <button @click="closeCarrierModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0" style="height: calc(100vh - 20rem); max-height: 60vh;">
            <!-- Available Carriers -->
            <div class="lg:col-span-3 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="carrierSearch" placeholder="Search carriers..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    <template x-if="loadingCarriers">
                        <div class="flex items-center justify-center py-12">
                            <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </template>
                    
                    <template x-if="!loadingCarriers && filteredCarriers.length === 0">
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                            <p>No carriers found</p>
                        </div>
                    </template>
                    
                    <template x-for="carrier in filteredCarriers" :key="carrier.id">
                        <div @click="toggleCarrier(carrier.id)" 
                             class="selection-item flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-600"
                             :class="{ 'selected': selectedCarriers.has(carrier.id) }">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="carrier.carrier_name || 'Unknown'"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <span x-text="carrier.dot_id ? 'DOT: ' + carrier.dot_id : 'No DOT ID'"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Carrier</span>
                                <template x-if="selectedCarriers.has(carrier.id)">
                                    <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Selected Carriers -->
            <div class="lg:col-span-2 bg-gray-50 dark:bg-gray-800/50 flex flex-col">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Selected (<span x-text="selectedCarriers.size"></span>)
                    </h3>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    <template x-if="selectedCarriers.size === 0">
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p class="text-sm">No carriers selected</p>
                        </div>
                    </template>
                    
                    <template x-for="carrier in carriers.filter(c => selectedCarriers.has(c.id))" :key="'selected-' + carrier.id">
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-900 dark:text-white text-sm" x-text="carrier.carrier_name"></span>
                            </div>
                            <button @click="toggleCarrier(carrier.id)" class="p-1 text-gray-400 hover:text-red-600 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
            <button @click="closeCarrierModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Cancel
            </button>
            <button @click="saveCarriers()" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Selection
            </button>
        </div>
    </div>
</div>
