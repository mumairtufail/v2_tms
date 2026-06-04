<!-- Enhanced Driver Selection Modal -->
<div x-show="showDriverModal" 
     class="v2-modal-backdrop"
     :class="{ 'show': showDriverModal }"
     @click.self="closeDriverModal()"
     @keydown.escape.window="closeDriverModal()"
     style="display: none;">
    
    <div class="v2-modal-content bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-5xl mx-4 overflow-hidden" style="max-height: calc(100vh - 4rem);">
        <!-- Header with gradient -->
        <div class="p-6 bg-gradient-to-r from-primary-600 to-primary-700 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Assign Drivers</h2>
                        <p class="text-primary-100 text-sm mt-1">Select drivers to add to this manifest</p>
                    </div>
                </div>
                <button @click="closeDriverModal()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Search Bar -->
            <div class="mt-6 relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       x-model="driverSearch" 
                       placeholder="Search by name or email..." 
                       class="w-full pl-12 pr-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-white/60 focus:bg-white/20 focus:border-white/40 transition-all">
            </div>
        </div>

        <!-- Content Area -->
        <div class="p-6" style="height: calc(100vh - 20rem); max-height: 500px; overflow-y: auto;">
            <!-- Loading State -->
            <template x-if="loadingDrivers">
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="relative">
                        <div class="w-16 h-16 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">Loading drivers...</p>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!loadingDrivers && filteredDrivers.length === 0">
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Drivers Found</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center max-w-sm">
                        <span x-show="driverSearch">No drivers match your search criteria.</span>
                        <span x-show="!driverSearch">No drivers available in your company. Add drivers from the Users section.</span>
                    </p>
                </div>
            </template>

            <!-- Drivers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!loadingDrivers && filteredDrivers.length > 0">
                <template x-for="driver in filteredDrivers" :key="driver.id">
                    <div @click="toggleDriver(driver.id)" 
                         class="group relative cursor-pointer p-4 border-2 rounded-xl transition-all duration-200"
                         :class="selectedDrivers.has(driver.id) ? 
                            'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md' : 
                            'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm'">
                        
                        <!-- Checkmark Badge -->
                        <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200"
                             :class="selectedDrivers.has(driver.id) ? 
                                'bg-primary-600 scale-100' : 
                                'bg-gray-200 dark:bg-gray-700 scale-0 group-hover:scale-100'">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20" x-show="selectedDrivers.has(driver.id)">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!selectedDrivers.has(driver.id)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Avatar -->
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center font-bold text-lg shadow-md ring-2 ring-white dark:ring-gray-800">
                                <span x-text="(driver.name || 'U').charAt(0).toUpperCase()"></span>
                            </div>
                            
                            <!-- Driver Info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 dark:text-white truncate" x-text="driver.name || 'Unknown'"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate" x-text="driver.email || 'No email'"></p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mt-3 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                Available
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer with selection summary and actions -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-3 py-2 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span class="font-semibold text-primary-700 dark:text-primary-300">
                            <span x-text="selectedDrivers.size"></span> Selected
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <span x-show="selectedDrivers.size === 0">No drivers selected</span>
                        <span x-show="selectedDrivers.size === 1">1 driver will be assigned</span>
                        <span x-show="selectedDrivers.size > 1" x-text="selectedDrivers.size + ' drivers will be assigned'"></span>
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button @click="closeDriverModal()" 
                            class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors font-medium">
                        Cancel
                    </button>
                    <button @click="saveDrivers()" 
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="selectedDrivers.size === 0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Assign <span x-show="selectedDrivers.size > 0" x-text="'(' + selectedDrivers.size + ')'"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
