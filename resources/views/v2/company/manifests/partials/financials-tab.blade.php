<div class="space-y-6" x-data="costEstimates({{ $manifest->costEstimates->toJson() }})">
    <!-- Cost Estimates Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cost Estimates</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track and manage manifest expenses</p>
                </div>
            </div>
            <button type="button" @click="addRow()" class="px-4 py-2 bg-green-50 hover:bg-green-100 text-green-600 dark:bg-green-900/20 dark:hover:bg-green-900/30 rounded-lg transition-colors font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Row
            </button>
        </div>
        
        <form action="{{ route('v2.manifests.update', ['company' => $company->slug, 'manifest' => $manifest->id]) }}" 
              method="POST" 
              @submit="submitting = true">
            @csrf
            @method('PATCH')
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Description</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Qty</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-40">Rate</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-40">Est. Cost</th>
                            <th class="px-6 py-4 w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <select x-model="row.type" 
                                            :name="'cost_estimates[' + index + '][type]'"
                                            class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2">
                                        <option value="fuel">Fuel</option>
                                        <option value="toll">Toll</option>
                                        <option value="driver_pay">Driver Pay</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="miscellaneous">Miscellaneous</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" x-model="row.description" 
                                           :name="'cost_estimates[' + index + '][description]'"
                                           placeholder="Enter description"
                                           class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" x-model.number="row.qty" 
                                           :name="'cost_estimates[' + index + '][qty]'"
                                           min="0" step="1"
                                           class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm text-right focus:border-primary-500 focus:ring-primary-500 px-3 py-2">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">$</span>
                                        <input type="number" x-model.number="row.rate" 
                                               :name="'cost_estimates[' + index + '][rate]'"
                                               min="0" step="0.01"
                                               class="w-full pl-8 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm text-right focus:border-primary-500 focus:ring-primary-500 px-3 py-2">
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    <span x-text="formatCurrency(row.qty * row.rate)"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" @click="removeRow(index)" 
                                            x-show="rows.length > 1"
                                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-t border-gray-200 dark:border-gray-800">
                        <tr>
                            <td colspan="4" class="px-6 py-5 text-right font-bold text-gray-500 dark:text-gray-400 tracking-widest uppercase text-xs">Total Estimated Cost</td>
                            <td class="px-6 py-5 text-right">
                                <span class="bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 px-6 py-2.5 rounded-xl font-bold text-2xl ring-1 ring-primary-500/20 shadow-sm" x-text="formatCurrency(total)"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end bg-gray-50/30 dark:bg-gray-900/30">
                <button type="submit" 
                        :disabled="submitting"
                        class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-all flex items-center gap-3 hover:shadow-xl hover:shadow-primary-500/25 disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-95">
                    <svg x-show="submitting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="submitting ? 'Saving Estimates...' : 'Save Estimates'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl transition-all duration-300 border-l-4 border-l-green-500">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center shadow-inner">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Est. Revenue</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">$0.00</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl transition-all duration-300 border-l-4 border-l-red-500">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-2xl flex items-center justify-center shadow-inner">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Est. Costs</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1" x-text="formatCurrency(total)">$0.00</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl transition-all duration-300 border-l-4 border-l-primary-500">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 bg-primary-100 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center shadow-inner">
                    <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Est. Profit</p>
                    <p class="text-2xl font-black text-primary-600 dark:text-primary-400 mt-1" x-text="formatCurrency(0 - total)">$0.00</p>
                </div>
            </div>
        </div>
    </div>
</div>
