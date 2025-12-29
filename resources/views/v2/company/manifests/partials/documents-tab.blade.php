<div class="space-y-6">
    <!-- Documents Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manifest Documents</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Generate and manage manifest related documents</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Rate Confirmation Card -->
                <div class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 hover:border-primary-500 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/20 rounded-xl flex items-center justify-center group-hover:bg-primary-600 transition-colors duration-300">
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 2v-6m-8 10H4a2 2 0 01-2-2V6a2 2 0 012-2h2m4.586 1H12m0 0h2m-2 0H10m4 0h2m-2 0h-2.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <span class="px-2.5 py-0.5 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 text-xs font-bold rounded-full uppercase tracking-wider">Ready</span>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Rate Confirmation</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Generate official rate confirmation document for the assigned carrier.</p>
                    <button @click="openRateConfirmationModal()" 
                            class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-primary-500/25 flex items-center justify-center gap-2 group/btn active:scale-95">
                        <svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View Rate Confirmation
                    </button>
                </div>

                <!-- Bill of Lading (Placeholder) -->
                <div class="bg-gray-50/50 dark:bg-gray-900/10 border border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-6 flex flex-col items-center justify-center text-center opacity-60">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h4 class="text-gray-900 dark:text-white font-bold mb-1">Bill of Lading</h4>
                    <p class="text-xs text-gray-500">Coming Soon</p>
                </div>
            </div>
        </div>
    </div>
</div>
