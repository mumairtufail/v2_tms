@php
    $manifestOrders = $manifest->orderStops->pluck('order')->filter()->unique('id');
    $manifestRevenue = $manifestOrders->sum(function ($order) {
        return optional($order->quote)
            ? $order->quote->costs->where('category', 'customer')->sum(function ($cost) {
                return (float) ($cost->cost ?? 0);
            })
            : 0;
    });
@endphp

<div class="space-y-6" x-data="costEstimates({{ $manifest->costEstimates->toJson() }}, {{ (float) $manifestRevenue }})">
    <!-- Cost Estimates Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cost Estimates</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track and manage manifest expenses</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-black px-3 py-1 rounded-full
                             bg-orange-100 text-orange-600 border border-orange-200
                             dark:bg-orange-950/60 dark:text-orange-400 dark:border-orange-800/40"
                      x-text="formatCurrency(total)"></span>
                <button type="button" @click="addRow()"
                        class="px-4 py-2 bg-orange-50 hover:bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:hover:bg-orange-900/30 rounded-lg transition-colors font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Line Item
                </button>
            </div>
        </div>

        <form action="{{ route('v2.manifests.update', ['company' => $company->slug, 'manifest' => $manifest->id]) }}"
              method="POST"
              @submit="submitting = true">
            @csrf
            @method('PATCH')

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left   text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-40">Type</th>
                            <th class="px-4 py-3 text-left   text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Description</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-28">Qty / %</th>
                            <th class="px-4 py-3 text-right  text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-36">Rate / Base</th>
                            <th class="px-4 py-3 text-right  text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest w-36">Cost</th>
                            <th class="px-4 py-3 w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="(row, idx) in rows" :key="idx">
                            <tr class="group hover:bg-orange-50/40 dark:hover:bg-gray-800/30 transition-colors">

                                {{-- TYPE --}}
                                <td class="px-4 py-3">
                                    {{-- Row 0: Freight --}}
                                    <template x-if="idx === 0">
                                        <select x-model="row.type"
                                                :name="'cost_estimates[' + idx + '][type]'"
                                                @change="row.description = row.type; normalizeRows()"
                                                class="w-full text-xs py-1.5 px-2 rounded-lg
                                                       border border-gray-200 dark:border-gray-600/40
                                                       bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                                       focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                            <option value="Freight">Freight</option>
                                            <option value="Freight (per mile)">Freight (per mile)</option>
                                        </select>
                                    </template>
                                    {{-- Row 1: Fuel --}}
                                    <template x-if="idx === 1">
                                        <select x-model="row.type"
                                                :name="'cost_estimates[' + idx + '][type]'"
                                                @change="normalizeRows()"
                                                class="w-full text-xs py-1.5 px-2 rounded-lg
                                                       border border-gray-200 dark:border-gray-600/40
                                                       bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                                       focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                            <option value="Fuel (surcharge)">Fuel (surcharge)</option>
                                            <option value="Fuel (per mile)">Fuel (per mile)</option>
                                            <option value="Fuel (flat)">Fuel (flat)</option>
                                        </select>
                                    </template>
                                    {{-- Row 2+: Miscellaneous --}}
                                    <template x-if="idx >= 2">
                                        <div>
                                            <input type="hidden" :name="'cost_estimates[' + idx + '][type]'" value="Miscellaneous">
                                            <div class="flex items-center gap-2 px-1">
                                                <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 shrink-0"></span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Miscellaneous</span>
                                            </div>
                                        </div>
                                    </template>
                                </td>

                                {{-- DESCRIPTION --}}
                                <td class="px-4 py-3">
                                    <input type="text" x-model="row.description"
                                           :name="'cost_estimates[' + idx + '][description]'"
                                           :readonly="row.type === 'Fuel (surcharge)' && idx === 1"
                                           :class="(row.type === 'Fuel (surcharge)' && idx === 1)
                                                    ? 'italic text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-transparent border-transparent cursor-not-allowed'
                                                    : 'border-gray-200 dark:border-gray-600/40 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'"
                                           class="w-full text-xs py-1.5 px-2 rounded-lg border"
                                           placeholder="Description...">
                                </td>

                                {{-- QTY / % --}}
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <input type="number" step="0.01" x-model="row.qty"
                                               :name="'cost_estimates[' + idx + '][qty]'"
                                               @input="normalizeRows()"
                                               :placeholder="(row.type === 'Fuel (surcharge)' && idx === 1) ? '%' : '0'"
                                               class="w-full text-xs py-1.5 px-2 rounded-lg text-center
                                                      border border-gray-200 dark:border-gray-600/40
                                                      bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                                      focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                        <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-bold text-orange-400 pointer-events-none select-none">%</span>
                                        </template>
                                    </div>
                                </td>

                                {{-- RATE --}}
                                <td class="px-4 py-3">
                                    {{-- Fuel surcharge: auto rate, read-only --}}
                                    <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                        <div>
                                            <input type="hidden" :name="'cost_estimates[' + idx + '][rate]'" :value="freightSubtotal().toFixed(2)">
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="text-xs text-gray-300 dark:text-gray-600 select-none">$</span>
                                                <div class="text-xs py-1.5 px-2 rounded-lg text-right w-24
                                                            border border-dashed border-orange-200 dark:border-orange-900/40
                                                            bg-orange-50/50 dark:bg-gray-800/20
                                                            text-orange-500/70 dark:text-orange-600/60 italic cursor-not-allowed"
                                                     x-text="parseFloat(freightSubtotal()).toFixed(2)"></div>
                                            </div>
                                        </div>
                                    </template>
                                    {{-- All other rows: editable rate --}}
                                    <template x-if="!(row.type === 'Fuel (surcharge)' && idx === 1)">
                                        <div class="flex items-center justify-end gap-1">
                                            <span class="text-xs text-gray-300 dark:text-gray-600 select-none">$</span>
                                            <input type="number" step="0.01" x-model="row.rate"
                                                   :name="'cost_estimates[' + idx + '][rate]'"
                                                   @input="normalizeRows()"
                                                   class="w-24 text-xs py-1.5 px-2 rounded-lg text-right
                                                          border border-gray-200 dark:border-gray-600/40
                                                          bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                                          focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                        </div>
                                    </template>
                                </td>

                                {{-- COST --}}
                                <td class="px-4 py-3 text-right font-bold text-orange-600 dark:text-orange-300">
                                    <span x-text="formatCurrency(calculateRowAmount(row))"></span>
                                </td>

                                {{-- ACTIONS (misc rows idx≥2 only) --}}
                                <td class="px-3 py-3">
                                    <template x-if="idx >= 2">
                                        <div class="flex items-center gap-1 justify-center">
                                            {{-- Duplicate --}}
                                            <button type="button" @click="duplicateRow(idx)" title="Duplicate"
                                                    class="w-6 h-6 flex items-center justify-center rounded-md transition-all
                                                           text-gray-300 hover:text-indigo-500 hover:bg-indigo-50
                                                           dark:text-gray-600 dark:hover:text-indigo-400 dark:hover:bg-indigo-950/40">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                            {{-- Delete (on hover) --}}
                                            <button type="button" @click="rows.splice(idx, 1); normalizeRows()" title="Delete"
                                                    class="w-6 h-6 flex items-center justify-center rounded-md opacity-0 group-hover:opacity-100 transition-all
                                                           text-gray-300 hover:text-red-500 hover:bg-red-50
                                                           dark:text-gray-600 dark:hover:text-red-400 dark:hover:bg-red-950/40">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="border-t-2 border-orange-200 dark:border-orange-900/30 bg-orange-50 dark:bg-orange-950/20">
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-right font-bold text-gray-500 dark:text-gray-400 tracking-widest uppercase text-xs">Total Cost</td>
                            <td class="px-4 py-4 text-right">
                                <span class="bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 px-5 py-2 rounded-xl font-black text-xl ring-1 ring-orange-400/20 shadow-sm"
                                      x-text="formatCurrency(total)"></span>
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
                    <span x-text="submitting ? 'Saving...' : 'Save Estimates'"></span>
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
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Revenue</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1" x-text="formatCurrency(revenue)">$0.00</p>
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
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Costs</p>
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
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Profit</p>
                    <p class="text-2xl font-black mt-1"
                       :class="profit >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-500 dark:text-red-400'"
                       x-text="formatCurrency(profit)">$0.00</p>
                </div>
            </div>
        </div>
    </div>
</div>
