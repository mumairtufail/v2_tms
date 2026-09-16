@php
    // Falls back to computing here when the partial is rendered outside the edit controller
    $financials = $financials ?? app(\App\Services\ManifestService::class)->financialSummary($manifest);

    $money = fn ($value) => '$' . number_format((float) $value, 2);

    $cardClass  = 'rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 shadow-sm';
    $headClass  = 'flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700';
    $thClass    = 'px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $inputClass = 'w-full text-xs py-1.5 px-2 rounded-md border border-gray-200 dark:border-gray-600/60 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
@endphp

<div class="space-y-5"
     x-data="costEstimates(@js($manifest->costEstimates), {{ $financials['revenue'] }})">

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="{{ $cardClass }} p-5">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/></svg>
                </span>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Revenue</p>
            </div>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="formatCurrency(revenue)"></p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Customer charges on this manifest's stops</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </span>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Costs</p>
            </div>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="formatCurrency(total)"></p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Carrier, fuel and extras on this manifest</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Profit</p>
            </div>
            <p class="mt-2 text-2xl font-semibold"
               :class="profit >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-600 dark:text-red-400'"
               x-text="formatCurrency(profit)"></p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500" x-text="marginLabel"></p>
        </div>
    </div>

    {{-- Manifest cost estimates --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headClass }}">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Manifest costs</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">What this trip costs you — carrier, fuel and extras</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-md bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="formatCurrency(total)"></span>
                <button type="button" @click="addRow()"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add line item
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
                    <thead class="bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="{{ $thClass }} text-left w-44">Type</th>
                            <th class="{{ $thClass }} text-left">Description</th>
                            <th class="{{ $thClass }} text-center w-24">Qty / %</th>
                            <th class="{{ $thClass }} text-right w-32">Rate / Base</th>
                            <th class="{{ $thClass }} text-right w-28">Cost</th>
                            <th class="{{ $thClass }} w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        <template x-for="(row, idx) in rows" :key="idx">
                            <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                                {{-- TYPE --}}
                                <td class="px-4 py-2.5">
                                    <template x-if="idx === 0">
                                        <select x-model="row.type"
                                                :name="'cost_estimates[' + idx + '][type]'"
                                                @change="row.description = row.type; normalizeRows()"
                                                class="{{ $inputClass }}">
                                            <option value="Freight">Freight</option>
                                            <option value="Freight (per mile)">Freight (per mile)</option>
                                        </select>
                                    </template>
                                    <template x-if="idx === 1">
                                        <select x-model="row.type"
                                                :name="'cost_estimates[' + idx + '][type]'"
                                                @change="normalizeRows()"
                                                class="{{ $inputClass }}">
                                            <option value="Fuel (surcharge)">Fuel (surcharge)</option>
                                            <option value="Fuel (per mile)">Fuel (per mile)</option>
                                            <option value="Fuel (flat)">Fuel (flat)</option>
                                        </select>
                                    </template>
                                    <template x-if="idx >= 2">
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" :name="'cost_estimates[' + idx + '][type]'" value="Miscellaneous">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Miscellaneous</span>
                                        </div>
                                    </template>
                                </td>

                                {{-- DESCRIPTION --}}
                                <td class="px-4 py-2.5">
                                    <input type="text" x-model="row.description"
                                           :name="'cost_estimates[' + idx + '][description]'"
                                           :readonly="row.type === 'Fuel (surcharge)' && idx === 1"
                                           :class="(row.type === 'Fuel (surcharge)' && idx === 1)
                                                    ? 'text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900/60 border-gray-200 dark:border-gray-700 cursor-not-allowed'
                                                    : ''"
                                           class="{{ $inputClass }}"
                                           placeholder="Description">
                                </td>

                                {{-- QTY / % --}}
                                <td class="px-4 py-2.5">
                                    <div class="relative">
                                        <input type="number" step="0.01" x-model="row.qty"
                                               :name="'cost_estimates[' + idx + '][qty]'"
                                               @input="normalizeRows()"
                                               :placeholder="(row.type === 'Fuel (surcharge)' && idx === 1) ? '%' : '0'"
                                               class="{{ $inputClass }} text-center">
                                        <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-gray-400 pointer-events-none">%</span>
                                        </template>
                                    </div>
                                </td>

                                {{-- RATE --}}
                                <td class="px-4 py-2.5">
                                    <template x-if="row.type === 'Fuel (surcharge)' && idx === 1">
                                        <div class="flex items-center justify-end gap-1">
                                            <input type="hidden" :name="'cost_estimates[' + idx + '][rate]'" :value="freightSubtotal().toFixed(2)">
                                            <span class="text-xs text-gray-400">$</span>
                                            <div class="w-24 rounded-md border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-2 py-1.5 text-right text-xs text-gray-400 dark:text-gray-500"
                                                 x-text="freightSubtotal().toFixed(2)"></div>
                                        </div>
                                    </template>
                                    <template x-if="!(row.type === 'Fuel (surcharge)' && idx === 1)">
                                        <div class="flex items-center justify-end gap-1">
                                            <span class="text-xs text-gray-400">$</span>
                                            <input type="number" step="0.01" x-model="row.rate" placeholder="0.00"
                                                   :name="'cost_estimates[' + idx + '][rate]'"
                                                   @input="normalizeRows()"
                                                   class="{{ $inputClass }} w-24 text-right">
                                        </div>
                                    </template>
                                </td>

                                {{-- COST --}}
                                <td class="px-4 py-2.5 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    <span x-text="formatCurrency(calculateRowAmount(row))"></span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="px-4 py-2.5">
                                    <template x-if="idx >= 2">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="duplicateRow(idx)" title="Duplicate"
                                                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                            <button type="button" @click="rows.splice(idx, 1); normalizeRows()" title="Remove"
                                                    class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="border-t border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Manifest total</td>
                            <td class="px-4 py-3 text-right text-base font-semibold text-gray-900 dark:text-white" x-text="formatCurrency(total)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-end px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                    <svg x-show="submitting" x-cloak class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Saving…' : 'Save costs'"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Linked orders --}}
    <div class="{{ $cardClass }}">
        <div class="{{ $headClass }}">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Orders on this manifest</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Customer revenue from these orders — edit it on the order</p>
            </div>
            <span class="rounded-md bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:text-gray-300">
                {{ count($financials['orders']) }} {{ Str::plural('order', count($financials['orders'])) }}
            </span>
        </div>

        @if(count($financials['orders']) === 0)
            <div class="px-5 py-10 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No orders are on this manifest yet.</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Assign order stops to it and their revenue and costs appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="{{ $thClass }} text-left">Order</th>
                            <th class="{{ $thClass }} text-left w-40">Stops here</th>
                            <th class="{{ $thClass }} text-right w-32">Revenue</th>
                            <th class="{{ $thClass }} text-right w-40">Share of manifest cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($financials['orders'] as $row)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $row['id']]) }}"
                                       class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                        #{{ $row['number'] }}
                                    </a>
                                    @if($row['customer'])
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $row['customer'] }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $row['stops_on_manifest'] }} of {{ $row['stops_total'] }}</span>
                                    @if($row['is_partial'])
                                        <span class="ml-1.5 rounded bg-amber-50 dark:bg-amber-900/30 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400"
                                              title="The rest of this order sits on other manifests, so only its share is counted here">
                                            {{ round($row['share'] * 100) }}%
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $money($row['revenue_share']) }}</span>
                                    @if($row['is_partial'])
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">of {{ $money($row['revenue_full']) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $money($row['manifest_cost_share']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $money($financials['revenue']) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $money($financials['manifest_cost']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div class="flex items-start gap-2 px-5 py-3 border-t border-gray-100 dark:border-gray-700">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
                When an order is split across manifests, only its share of the stops is counted here, and this manifest's own costs are split across its orders — so nothing is counted twice.
            </p>
        </div>
    </div>
</div>
