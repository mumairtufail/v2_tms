{{-- resources/views/v2/company/orders/partials/quote-modal.blade.php --}}
{{--
    Row Logic (handled by Alpine.js in form.blade.php):
      Row 0 (idx=0) → Freight|Freight(per mile)  — cost = qty × rate
      Row 1 (idx=1) → Fuel(surcharge)|Fuel(per mile)|Fuel(flat)
                       Fuel(surcharge): rate = freightSubtotal (auto, disabled)
                                        qty  = surcharge percentage
                                        cost = freightSubtotal × qty% / 100
                                        normalizeQuoteRows() drives all this
      Row 2+ (idx≥2) → Miscellaneous — has copy + delete buttons
--}}

<div x-cloak x-show="showProcessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div x-show="showProcessModal"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm dark:bg-black/80"
         @click="showProcessModal = false"></div>

    {{-- Modal Panel --}}
    <div x-show="showProcessModal"
         x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-[1440px] max-h-[92vh] flex flex-col
                bg-white dark:bg-[#0f172a]
                rounded-2xl shadow-2xl shadow-slate-400/30 dark:shadow-black/60
                border border-slate-200 dark:border-slate-700/50 overflow-hidden">

        {{-- ════════════════════ HEADER ════════════════════ --}}
        <div class="flex items-center justify-between px-5 py-3 shrink-0
                    bg-white dark:bg-[#0f172a] border-b border-slate-200 dark:border-slate-700/60">
            <div class="flex items-center gap-3">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-50"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-500"></span>
                </span>
                <h3 class="text-[13px] font-extrabold uppercase tracking-widest text-slate-800 dark:text-slate-100">
                    Order Processing &amp; Quoting
                </h3>
                <span class="hidden sm:block text-[11px] text-slate-400 dark:text-slate-500">Assign manifests and define financial quotes</span>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="flex items-center gap-px rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700/50 shadow-sm">
                    <div class="flex flex-col items-end px-3 py-1.5 bg-slate-50 dark:bg-slate-800/80">
                        <span class="text-[8px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 leading-none">Net Profit</span>
                        <span class="text-[13px] font-black leading-tight mt-0.5"
                              :class="calculateProfit() >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'"
                              x-text="'$' + formatMoney(calculateProfit())"></span>
                    </div>
                    <div class="w-px self-stretch bg-slate-200 dark:bg-slate-700"></div>
                    <div class="flex flex-col items-end px-3 py-1.5 bg-slate-50 dark:bg-slate-800/80">
                        <span class="text-[8px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 leading-none">Margin</span>
                        <span class="text-[13px] font-black leading-tight mt-0.5"
                              :class="calculateMargin() >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'"
                              x-text="calculateMargin() + '%'"></span>
                    </div>
                </div>
                <button @click="showProcessModal = false"
                        class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors
                               text-slate-400 hover:text-slate-700 hover:bg-slate-100
                               dark:text-slate-500 dark:hover:text-slate-200 dark:hover:bg-slate-700/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ════════════════════ SUB-HEADER ════════════════════ --}}
        <div class="px-5 py-2.5 shrink-0 flex items-center gap-4 flex-wrap
                    bg-slate-50 dark:bg-slate-800/30 border-b border-slate-200 dark:border-slate-700/60">
            <div class="flex items-center gap-2">
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 whitespace-nowrap">Service</label>
                <select x-model="quote.service_id"
                        class="text-[11px] py-1 px-2 rounded-lg border border-slate-300 dark:border-slate-600/60
                               bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                               focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 min-w-[140px] shadow-sm">
                    <option value="">Select Service...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 whitespace-nowrap">Est. Start</label>
                <input type="datetime-local" x-model="quote.delivery_start"
                       class="text-[11px] py-1 px-2 rounded-lg border border-slate-300 dark:border-slate-600/60
                              bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                              focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 shadow-sm">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 whitespace-nowrap">Est. End</label>
                <input type="datetime-local" x-model="quote.delivery_end"
                       class="text-[11px] py-1 px-2 rounded-lg border border-slate-300 dark:border-slate-600/60
                              bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                              focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 shadow-sm">
            </div>
            <div class="flex items-center gap-2 ml-auto flex-wrap">
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 whitespace-nowrap">Mass Manifest</label>
                <select x-model="massManifestId"
                        class="text-[11px] py-1 px-2 rounded-lg border border-slate-300 dark:border-slate-600/60
                               bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                               focus:ring-2 focus:ring-indigo-400 shadow-sm">
                    <option value="">Apply to all...</option>
                    <template x-for="manifest in manifests" :key="manifest.id">
                        <option :value="String(manifest.id)" x-text="manifest.code"></option>
                    </template>
                </select>
                <button type="button" @click="applyMassManifest()"
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg shadow-sm transition-colors bg-indigo-600 text-white hover:bg-indigo-700">Apply</button>
                <button type="button" @click="createPendingManifest()"
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg shadow-sm transition-colors bg-emerald-600 text-white hover:bg-emerald-700">+ New</button>
            </div>
        </div>

        {{-- ════════════════════ MAIN BODY ════════════════════ --}}
        <div class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900/30">
            <div class="grid grid-cols-2 min-h-full divide-x divide-slate-200 dark:divide-slate-700/50">

                {{-- ═══════════════════════════════════════════════
                     CARRIER COST — Orange
                     ═══════════════════════════════════════════════ --}}
                <div class="flex flex-col">
                    <div class="flex items-center justify-between px-4 py-2.5 shrink-0
                                bg-orange-50/80 dark:bg-orange-950/30
                                border-b border-orange-200/60 dark:border-orange-900/30">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-orange-500 shadow-sm shadow-orange-400/50"></div>
                            <h4 class="text-[11px] font-black uppercase tracking-widest text-orange-600 dark:text-orange-400">Carrier Cost</h4>
                            <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-500 dark:bg-orange-900/40 dark:text-orange-400">Expenses</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full
                                         bg-orange-100 text-orange-600 border border-orange-200
                                         dark:bg-orange-950/60 dark:text-orange-400 dark:border-orange-800/40"
                                  x-text="'$' + formatMoney(calculateTotal(quote.carrier_rows))"></span>
                            <button type="button" @click="addQuoteRow('carrier')"
                                    class="flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-lg transition-all shadow-sm
                                           bg-white text-slate-600 border border-slate-300 hover:bg-orange-50 hover:border-orange-300 hover:text-orange-700
                                           dark:bg-slate-700/60 dark:border-slate-600/50 dark:text-slate-300 dark:hover:bg-slate-600/60 dark:hover:text-white">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                Add Line Item
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 bg-white dark:bg-transparent overflow-x-auto">
                        <table class="w-full text-[11px]">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-slate-100 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700/50">
                                    <th class="px-3 py-2 text-left   text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[130px]">Type</th>
                                    <th class="px-3 py-2 text-left   text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Description</th>
                                    <th class="px-3 py-2 text-center text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[70px]">Qty / %</th>
                                    <th class="px-3 py-2 text-right  text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[96px]">Rate</th>
                                    <th class="px-3 py-2 text-right  text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[100px]">Cost (CAD)</th>
                                    <th class="px-2 py-2 w-[52px]"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/30">
                                <template x-for="(row, idx) in quote.carrier_rows" :key="'c-' + idx">
                                    <tr class="group transition-colors hover:bg-orange-50/50 dark:hover:bg-slate-800/40">

                                        {{-- TYPE --}}
                                        <td class="px-2 py-1.5">
                                            {{-- Row 0: Freight types --}}
                                            <template x-if="idx === 0">
                                                <select x-model="row.type"
                                                        @change="row.description = row.type; normalizeQuoteRows(quote.carrier_rows)"
                                                        class="w-full text-[10px] py-1 px-1.5 rounded-md
                                                               border border-slate-200 dark:border-slate-600/40
                                                               bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                               focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                                    <option value="Freight">Freight</option>
                                                    <option value="Freight (per mile)">Freight (per mile)</option>
                                                </select>
                                            </template>
                                            {{-- Row 1: Fuel types --}}
                                            <template x-if="idx === 1">
                                                <select x-model="row.type"
                                                        @change="normalizeQuoteRows(quote.carrier_rows)"
                                                        class="w-full text-[10px] py-1 px-1.5 rounded-md
                                                               border border-slate-200 dark:border-slate-600/40
                                                               bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                               focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                                    <option value="Fuel (surcharge)">Fuel (surcharge)</option>
                                                    <option value="Fuel (per mile)">Fuel (per mile)</option>
                                                    <option value="Fuel (flat)">Fuel (flat)</option>
                                                </select>
                                            </template>
                                            {{-- Row 2+: Miscellaneous (label only) --}}
                                            <template x-if="idx >= 2">
                                                <div class="flex items-center gap-1.5 px-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Miscellaneous</span>
                                                </div>
                                            </template>
                                        </td>

                                        {{-- DESCRIPTION --}}
                                        <td class="px-2 py-1.5">
                                            <input type="text" x-model="row.description"
                                                   :readonly="row.type === 'Fuel (surcharge)' && idx === 1"
                                                   class="w-full text-[10px] py-1 px-1.5 rounded-md
                                                          border border-slate-200 dark:border-slate-600/40
                                                          bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                          placeholder-slate-300 dark:placeholder-slate-600
                                                          focus:ring-2 focus:ring-orange-400 focus:border-orange-400"
                                                   :class="(row.type === 'Fuel (surcharge)' && idx === 1)
                                                            ? 'italic text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-transparent border-transparent cursor-not-allowed'
                                                            : ''"
                                                   placeholder="Description...">
                                        </td>

                                        {{-- QTY / % --}}
                                        <td class="px-2 py-1.5">
                                            <div class="relative">
                                                <input type="number" step="0.01" x-model="row.qty"
                                                       @focus="$event.target.select()"
                                                       @input="normalizeQuoteRows(quote.carrier_rows)"
                                                       class="w-full text-[10px] py-1 px-1.5 rounded-md text-center
                                                              border border-slate-200 dark:border-slate-600/40
                                                              bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                              focus:ring-2 focus:ring-orange-400 focus:border-orange-400"
                                                       :placeholder="(row.type === 'Fuel (surcharge)' && idx === 1) ? '%' : '1'">
                                                {{-- % suffix badge for fuel surcharge --}}
                                                <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                                    <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px] font-bold text-orange-400 pointer-events-none select-none">%</span>
                                                </template>
                                            </div>
                                        </td>

                                        {{-- RATE --}}
                                        <td class="px-2 py-1.5">
                                            <div class="flex items-center gap-0.5 justify-end">
                                                <span class="text-[10px] text-slate-300 dark:text-slate-600 select-none">$</span>
                                                {{-- Fuel surcharge: rate is auto-set by normalizeQuoteRows, show as read-only --}}
                                                <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                                    <input type="number" step="0.01" x-model="row.rate" readonly
                                                           class="w-20 text-[10px] py-1 px-1 rounded-md text-right
                                                                  border border-dashed border-orange-200 dark:border-orange-900/40
                                                                  bg-orange-50/50 dark:bg-slate-800/20
                                                                  text-orange-500/70 dark:text-orange-600/60
                                                                  cursor-not-allowed italic">
                                                </template>
                                                {{-- All other rows: normal editable rate --}}
                                                <template x-if="!(row.type === 'Fuel (surcharge)' && idx === 1)">
                                                    <input type="number" step="0.01" x-model="row.rate"
                                                           @input="normalizeQuoteRows(quote.carrier_rows)"
                                                           class="w-20 text-[10px] py-1 px-1 rounded-md text-right
                                                                  border border-slate-200 dark:border-slate-600/40
                                                                  bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                                  focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                                                </template>
                                            </div>
                                        </td>

                                        {{-- COST (CAD) — always from normalizeQuoteRows via calculateRowAmount --}}
                                        <td class="px-2 py-1.5 text-right font-bold text-orange-600 dark:text-orange-300"
                                            x-text="'$' + formatMoney(calculateRowAmount(row, quote.carrier_rows))">
                                        </td>

                                        {{-- ACTION BUTTONS (only for misc rows idx≥2) --}}
                                        <td class="px-1.5 py-1.5">
                                            <template x-if="idx >= 2">
                                                <div class="flex items-center gap-0.5 justify-center">
                                                    {{-- Duplicate --}}
                                                    <button type="button"
                                                            @click="duplicateQuoteRow('carrier', idx)"
                                                            title="Duplicate"
                                                            class="w-5 h-5 flex items-center justify-center rounded-md transition-all
                                                                   text-slate-300 hover:text-indigo-500 hover:bg-indigo-50
                                                                   dark:text-slate-600 dark:hover:text-indigo-400 dark:hover:bg-indigo-950/40">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </button>
                                                    {{-- Delete (appears on hover) --}}
                                                    <button type="button"
                                                            @click="quote.carrier_rows.splice(idx, 1); normalizeQuoteRows(quote.carrier_rows)"
                                                            class="w-5 h-5 flex items-center justify-center rounded-md opacity-0 group-hover:opacity-100 transition-all
                                                                   text-slate-300 hover:text-red-500 hover:bg-red-50
                                                                   dark:text-slate-600 dark:hover:text-red-400 dark:hover:bg-red-950/40">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-orange-200 dark:border-orange-900/30 bg-orange-50 dark:bg-orange-950/20">
                                    <td colspan="4" class="px-3 py-2 text-right text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Cost Total</td>
                                    <td class="px-3 py-2 text-right font-black text-[13px] text-orange-600 dark:text-orange-400"
                                        x-text="'$' + formatMoney(calculateTotal(quote.carrier_rows))"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════
                     CUSTOMER QUOTE — Indigo
                     (Identical structure, bound to customer_rows)
                     ═══════════════════════════════════════════════ --}}
                <div class="flex flex-col">
                    <div class="flex items-center justify-between px-4 py-2.5 shrink-0
                                bg-indigo-50/80 dark:bg-indigo-950/30
                                border-b border-indigo-200/60 dark:border-indigo-900/30">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 shadow-sm shadow-indigo-400/50"></div>
                            <h4 class="text-[11px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Customer Quote</h4>
                            <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-500 dark:bg-indigo-900/40 dark:text-indigo-400">Revenue</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full
                                         bg-indigo-100 text-indigo-600 border border-indigo-200
                                         dark:bg-indigo-950/60 dark:text-indigo-400 dark:border-indigo-800/40"
                                  x-text="'$' + formatMoney(calculateTotal(quote.customer_rows))"></span>
                            <button type="button" @click="addQuoteRow('customer')"
                                    class="flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-lg transition-all shadow-sm
                                           bg-white text-slate-600 border border-slate-300 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700
                                           dark:bg-slate-700/60 dark:border-slate-600/50 dark:text-slate-300 dark:hover:bg-slate-600/60 dark:hover:text-white">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                Add Line Item
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 bg-white dark:bg-transparent overflow-x-auto">
                        <table class="w-full text-[11px]">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-slate-100 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700/50">
                                    <th class="px-3 py-2 text-left   text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[130px]">Type</th>
                                    <th class="px-3 py-2 text-left   text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Description</th>
                                    <th class="px-3 py-2 text-center text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[70px]">Qty / %</th>
                                    <th class="px-3 py-2 text-right  text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[96px]">Rate</th>
                                    <th class="px-3 py-2 text-right  text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 w-[100px]">Cost (CAD)</th>
                                    <th class="px-2 py-2 w-[52px]"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/30">
                                <template x-for="(row, idx) in quote.customer_rows" :key="'q-' + idx">
                                    <tr class="group transition-colors hover:bg-indigo-50/50 dark:hover:bg-slate-800/40">

                                        {{-- TYPE --}}
                                        <td class="px-2 py-1.5">
                                            <template x-if="idx === 0">
                                                <select x-model="row.type"
                                                        @change="row.description = row.type; normalizeQuoteRows(quote.customer_rows)"
                                                        class="w-full text-[10px] py-1 px-1.5 rounded-md
                                                               border border-slate-200 dark:border-slate-600/40
                                                               bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                               focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                                    <option value="Freight">Freight</option>
                                                    <option value="Freight (per mile)">Freight (per mile)</option>
                                                </select>
                                            </template>
                                            <template x-if="idx === 1">
                                                <select x-model="row.type"
                                                        @change="normalizeQuoteRows(quote.customer_rows)"
                                                        class="w-full text-[10px] py-1 px-1.5 rounded-md
                                                               border border-slate-200 dark:border-slate-600/40
                                                               bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                               focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                                    <option value="Fuel (surcharge)">Fuel (surcharge)</option>
                                                    <option value="Fuel (per mile)">Fuel (per mile)</option>
                                                    <option value="Fuel (flat)">Fuel (flat)</option>
                                                </select>
                                            </template>
                                            <template x-if="idx >= 2">
                                                <div class="flex items-center gap-1.5 px-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Miscellaneous</span>
                                                </div>
                                            </template>
                                        </td>

                                        {{-- DESCRIPTION --}}
                                        <td class="px-2 py-1.5">
                                            <input type="text" x-model="row.description"
                                                   :readonly="row.type === 'Fuel (surcharge)' && idx === 1"
                                                   class="w-full text-[10px] py-1 px-1.5 rounded-md
                                                          border border-slate-200 dark:border-slate-600/40
                                                          bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                          placeholder-slate-300 dark:placeholder-slate-600
                                                          focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                                   :class="(row.type === 'Fuel (surcharge)' && idx === 1)
                                                            ? 'italic text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-transparent border-transparent cursor-not-allowed'
                                                            : ''"
                                                   placeholder="Description...">
                                        </td>

                                        {{-- QTY / % --}}
                                        <td class="px-2 py-1.5">
                                            <div class="relative">
                                                <input type="number" step="0.01" x-model="row.qty"
                                                       @focus="$event.target.select()"
                                                       @input="normalizeQuoteRows(quote.customer_rows)"
                                                       class="w-full text-[10px] py-1 px-1.5 rounded-md text-center
                                                              border border-slate-200 dark:border-slate-600/40
                                                              bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                              focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                                       :placeholder="(row.type === 'Fuel (surcharge)' && idx === 1) ? '%' : '1'">
                                                <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                                    <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px] font-bold text-indigo-400 pointer-events-none select-none">%</span>
                                                </template>
                                            </div>
                                        </td>

                                        {{-- RATE --}}
                                        <td class="px-2 py-1.5">
                                            <div class="flex items-center gap-0.5 justify-end">
                                                <span class="text-[10px] text-slate-300 dark:text-slate-600 select-none">$</span>
                                                <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                                    <input type="number" step="0.01" x-model="row.rate" readonly
                                                           class="w-20 text-[10px] py-1 px-1 rounded-md text-right
                                                                  border border-dashed border-indigo-200 dark:border-indigo-900/40
                                                                  bg-indigo-50/50 dark:bg-slate-800/20
                                                                  text-indigo-500/70 dark:text-indigo-600/60
                                                                  cursor-not-allowed italic">
                                                </template>
                                                <template x-if="!(row.type === 'Fuel (surcharge)' && idx === 1)">
                                                    <input type="number" step="0.01" x-model="row.rate"
                                                           @input="normalizeQuoteRows(quote.customer_rows)"
                                                           class="w-20 text-[10px] py-1 px-1 rounded-md text-right
                                                                  border border-slate-200 dark:border-slate-600/40
                                                                  bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-200
                                                                  focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                                </template>
                                            </div>
                                        </td>

                                        {{-- COST (CAD) --}}
                                        <td class="px-2 py-1.5 text-right font-bold text-indigo-600 dark:text-indigo-300"
                                            x-text="'$' + formatMoney(calculateRowAmount(row, quote.customer_rows))">
                                        </td>

                                        {{-- ACTION BUTTONS --}}
                                        <td class="px-1.5 py-1.5">
                                            <template x-if="idx >= 2">
                                                <div class="flex items-center gap-0.5 justify-center">
                                                    <button type="button"
                                                            @click="duplicateQuoteRow('customer', idx)"
                                                            title="Duplicate"
                                                            class="w-5 h-5 flex items-center justify-center rounded-md transition-all
                                                                   text-slate-300 hover:text-indigo-500 hover:bg-indigo-50
                                                                   dark:text-slate-600 dark:hover:text-indigo-400 dark:hover:bg-indigo-950/40">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button"
                                                            @click="quote.customer_rows.splice(idx, 1); normalizeQuoteRows(quote.customer_rows)"
                                                            class="w-5 h-5 flex items-center justify-center rounded-md opacity-0 group-hover:opacity-100 transition-all
                                                                   text-slate-300 hover:text-red-500 hover:bg-red-50
                                                                   dark:text-slate-600 dark:hover:text-red-400 dark:hover:bg-red-950/40">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-indigo-200 dark:border-indigo-900/30 bg-indigo-50 dark:bg-indigo-950/20">
                                    <td colspan="4" class="px-3 py-2 text-right text-[9px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Quote Total</td>
                                    <td class="px-3 py-2 text-right font-black text-[13px] text-indigo-600 dark:text-indigo-400"
                                        x-text="'$' + formatMoney(calculateTotal(quote.customer_rows))"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>{{-- END grid --}}

            {{-- ════════════════════ MANIFEST STRIP ════════════════════ --}}
            <div class="border-t border-slate-200 dark:border-slate-700/50 bg-white dark:bg-slate-800/20 px-4 py-2.5">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest whitespace-nowrap text-slate-400 dark:text-slate-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Manifest Assignments
                    </span>
                    <template x-for="(stop, sIdx) in stops" :key="'ms-' + sIdx">
                        <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg
                                    bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/40">
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap" x-text="'Stop ' + (sIdx + 1)"></span>
                            <span class="text-[9px] text-slate-300 dark:text-slate-600"
                                  x-text="(stop.shipper?.city || '\u2013') + ' \u2192 ' + (stop.consignee?.city || '\u2013')"></span>
                            {{-- Use :value + @change instead of x-model to avoid integer vs string mismatch --}}
                            <select
                                :value="String(stop.manifest_id || '')"
                                @change="stop.manifest_id = $event.target.value"
                                class="text-[10px] py-0.5 px-1.5 rounded-md
                                       border border-slate-200 dark:border-slate-600/40
                                       bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200
                                       focus:ring-1 focus:ring-indigo-400 min-w-[110px]">
                                <option value="">No Manifest</option>
                                <template x-for="manifest in manifests" :key="String(manifest.id)">
                                    <option
                                        :value="String(manifest.id)"
                                        :selected="String(stop.manifest_id || '') === String(manifest.id)"
                                        x-text="manifest.code">
                                    </option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>
            </div>
        </div>{{-- END flex-1 --}}

        {{-- ════════════════════ FOOTER ════════════════════ --}}
        <div class="flex items-center justify-between px-5 py-3 shrink-0
                    border-t border-slate-200 dark:border-slate-700/60
                    bg-slate-50 dark:bg-slate-900/80">
            <p class="text-[10px] italic text-slate-400 dark:text-slate-600 max-w-md">
                <!-- Rows 1 &amp; 2 are fixed (Freight + Fuel). "Add Line Item" appends a Miscellaneous row with copy &amp; delete. -->
            </p>
            <div class="flex items-center gap-2">
                <button type="button" @click="showProcessModal = false"
                        class="px-4 py-1.5 text-[11px] font-bold rounded-lg transition-all shadow-sm
                               text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700
                               dark:text-slate-400 dark:bg-transparent dark:border-slate-600/60 dark:hover:bg-slate-700/40 dark:hover:text-slate-200">
                    Cancel
                </button>
                <button type="button" @click="submitQuote()" :disabled="submitting"
                        class="flex items-center gap-2 px-5 py-1.5 text-[11px] font-bold rounded-lg transition-all
                               bg-gradient-to-r from-indigo-600 to-indigo-500 text-white
                               hover:from-indigo-700 hover:to-indigo-600
                               shadow-md shadow-indigo-400/30 dark:shadow-indigo-900/50
                               disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="submitting" class="animate-spin">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <svg x-show="!submitting" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Publish Quote
                </button>
            </div>
        </div>

    </div>
</div>
