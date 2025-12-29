@extends('v2.layouts.app')

@section('title', isset($order) ? "Edit Order #{$order->order_number}" : "New Order Draft")

@section('content')
<div class="space-y-6" x-data="orderForm()">
    {{-- 1. Breadcrumb --}}
    <x-v2-breadcrumb :items="[
        ['label' => 'Orders', 'url' => route('v2.orders.index', $company)],
        ['label' => isset($order) ? 'Edit Order' : 'New Order']
    ]" />

    {{-- 2. Page Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('v2.orders.index', $company) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <x-page-header :title="isset($order) ? 'Order #' . $order->order_number : 'New Order Draft'" 
                          :description="'Manage details for ' . ($order->customer->name ?? 'Unknown')" />
        </div>
        
        <div class="flex items-center gap-2">
            <x-secondary-button @click="saveDraft()" type="button">
                <span x-show="saving" class="mr-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </span>
                Save as Draft
            </x-secondary-button>
            <x-primary-button @click="openConfirmModal()">
                Submit & Process
            </x-primary-button>
        </div>
    </div>

    {{-- 3. Order Type Tabs --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="flex -mb-px" aria-label="Order Types">
                @php
                    $orderTypes = [
                        'point_to_point' => 'Point-to-Point',
                        'single_shipper' => 'Single Shipper',
                        'single_consignee' => 'Single Consignee',
                        'sequence' => 'Sequence',
                    ];
                @endphp
                @foreach($orderTypes as $typeKey => $typeLabel)
                    <button type="button"
                        class="flex-1 py-3 px-4 text-center text-sm font-medium border-b-2 transition-colors
                            {{ $order->order_type === $typeKey 
                                ? 'border-primary-500 text-primary-600 bg-primary-50 dark:bg-primary-900/20' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                        {{ $order->order_type === $typeKey ? 'disabled' : '' }}
                        @if($order->order_type !== $typeKey)
                            onclick="window.location.href='{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]) }}?type={{ $typeKey }}'"
                        @endif
                    >
                        {{ $typeLabel }}
                    </button>
                @endforeach
            </nav>
        </div>
        
        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 text-xs text-gray-500">
            <span class="font-medium">{{ $orderTypes[$order->order_type] ?? 'Unknown' }}:</span>
            @switch($order->order_type)
                @case('point_to_point')
                    Simple 1 pickup → 1 delivery flow.
                    @break
                @case('single_shipper')
                    1 pickup stop with multiple delivery destinations.
                    @break
                @case('single_consignee')
                    Multiple pickup locations delivered to a single destination.
                    @break
                @case('sequence')
                    A sequence of stops, each stop starts where the previous one ended.
                    @break
            @endswitch
        </div>
    </div>

    {{-- 4. Main Form --}}
    <form id="orderForm" action="{{ route('v2.orders.update', ['company' => $company->slug, 'order' => $order->id]) }}" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="order_type" value="{{ $order->order_type }}">
        <input type="hidden" name="save_as_draft" x-bind:value="saving ? '1' : '0'">
        <input type="hidden" name="stops" x-bind:value="JSON.stringify(stops)">
        <input type="hidden" name="quote_data" x-bind:value="JSON.stringify(quote)">
        
        <div class="grid grid-cols-1 gap-6">
            {{-- General Info Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Order References</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Reference Number</label>
                        <input type="text" name="ref_number" value="{{ old('ref_number', $order->ref_number) }}" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="Internal/Customer Ref">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Customer PO</label>
                        <input type="text" name="customer_po_number" value="{{ old('customer_po_number', $order->customer_po_number) }}" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="PO Number">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Special Instructions</label>
                        <textarea name="special_instructions" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" rows="1">{{ old('special_instructions', $order->special_instructions) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Voyage Legs (Stops) Section --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Voyage Legs (Stops)</h3>
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded text-xs" x-text="stops.length + ' Legs'"></span>
                    </div>
                </div>

                {{-- Stops Summary Table (Rose Rocket style) --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Stop #</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Shipper</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Consignee</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Manifest</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Items</th>
                                <th class="px-3 py-2 text-center text-[10px] font-bold text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(stop, stopIndex) in stops" :key="stop.uid">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer" @click="stop.expanded = !stop.expanded">
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-[10px] font-bold" x-text="stopIndex + 1"></span>
                                    </td>
                                    <td class="px-3 py-2" x-text="stop.shipper.city ? stop.shipper.city + ', ' + stop.shipper.state : '-'"></td>
                                    <td class="px-3 py-2" x-text="stop.consignee.city ? stop.consignee.city + ', ' + stop.consignee.state : '-'"></td>
                                    <td class="px-3 py-2" x-text="stop.manifest_id || '-'"></td>
                                    <td class="px-3 py-2" x-text="stop.commodities.length"></td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Draft</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Dynamic Stops List (Expanded Details) --}}
                <div class="space-y-6">
                    <template x-for="(stop, stopIndex) in stops" :key="stop.uid">
                        <div class="relative">
                            {{-- Connection Line --}}
                            <div x-show="stopIndex < stops.length - 1" class="absolute left-6 top-12 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-800 z-0"></div>
                            
                            {{-- Stop Card --}}
                            <div class="relative z-10 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 transition-all hover:shadow-md overflow-hidden" :class="stop.expanded ? 'ring-2 ring-primary-500' : 'group'">
                                {{-- Stop Header --}}
                                <div @click="stop.expanded = !stop.expanded" class="cursor-pointer p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold" x-text="stopIndex + 1"></div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="stop.shipper.company_name || 'Empty Shipper'"></span>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="stop.consignee.company_name || 'Empty Consignee'"></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px] text-gray-500 mt-0.5">
                                                <span x-text="stop.commodities.length + ' items'"></span>
                                                <span>•</span>
                                                <span x-text="(stop.shipper.city || 'N/A') + ', ' + (stop.shipper.state || '')"></span>
                                                <span>→</span>
                                                <span x-text="(stop.consignee.city || 'N/A') + ', ' + (stop.consignee.state || '')"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click.stop="removeStop(stopIndex)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="stop.expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>

                                {{-- Stop Body (Expanded) --}}
                                <div x-show="stop.expanded" x-collapse>
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800 space-y-6 bg-gray-50/30 dark:bg-gray-800/10">
                                        
                                        {{-- Shipper & Consignee Side by Side --}}
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            {{-- Shipper Section --}}
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full bg-green-500 ring-4 ring-green-100 dark:ring-green-900/30"></div>
                                                    <h4 class="text-xs font-bold text-gray-500 uppercase">Shipper Information (Pickup)</h4>
                                                </div>
                                                @include('v2.company.orders.partials.location-fields', ['prefix' => 'shipper'])
                                            </div>

                                            {{-- Consignee Section --}}
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full bg-blue-500 ring-4 ring-blue-100 dark:ring-blue-900/30"></div>
                                                    <h4 class="text-xs font-bold text-gray-500 uppercase">Consignee Information (Delivery)</h4>
                                                </div>
                                                @include('v2.company.orders.partials.location-fields', ['prefix' => 'consignee'])
                                            </div>
                                        </div>

                                        {{-- Additional & Billing Details --}}
                                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-2 h-2 rounded-full bg-yellow-500 ring-4 ring-yellow-100 dark:ring-yellow-900/30"></div>
                                                <h4 class="text-xs font-bold text-gray-500 uppercase">Additional & Billing Details</h4>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Customs Broker</label>
                                                        <input type="text" x-model="stop.billing.customs_broker" placeholder="Customs broker" class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Port of Entry</label>
                                                        <input type="text" x-model="stop.billing.port_of_entry" placeholder="Port of entry" class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Declared value</label>
                                                            <div class="relative">
                                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                                                <input type="number" step="0.01" x-model="stop.billing.declared_value" class="pl-7 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="0.00">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Currency</label>
                                                            <select x-model="stop.billing.currency" class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                                <option value="USD">USD</option>
                                                                <option value="CAD">CAD</option>
                                                                <option value="EUR">EUR</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Container Number</label>
                                                        <input type="text" x-model="stop.billing.container_number" placeholder="Container Number" class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">REF Number</label>
                                                        <input type="text" x-model="stop.billing.ref_number" placeholder="REF Number" class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Customer Po Number</label>
                                                        <input type="text" x-model="stop.billing.customer_po_number" placeholder="Customer Po Number" class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Commodities Section - Rose Rocket Style --}}
                                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                                                {{-- Commodities Header with Count --}}
                                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                                            Commodities (<span x-text="stop.commodities.length"></span>)
                                                        </h4>
                                                        <button type="button" @click="addCommodity(stopIndex)" class="text-xs text-primary-600 hover:text-primary-700 font-bold flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                            + Add commodity
                                                        </button>
                                                    </div>
                                                    
                                                    {{-- Service Type & Measurements Row --}}
                                                    <div class="flex flex-wrap items-center gap-4 mt-3">
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-[10px] font-medium text-gray-500 uppercase">Service type *</label>
                                                            <select x-model="stop.service_type" class="text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md px-2 py-1 focus:ring-primary-500 focus:border-primary-500">
                                                                <option value="truckload">Truckload</option>
                                                                <option value="ltl">LTL</option>
                                                                <option value="cube">Cube</option>
                                                            </select>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-[10px] font-medium text-gray-500 uppercase">Measurements *</label>
                                                            <select x-model="stop.measurements" class="text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md px-2 py-1 focus:ring-primary-500 focus:border-primary-500">
                                                                <option value="in_lbs">in/lbs</option>
                                                                <option value="cm_kg">cm/kg</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Commodity Table --}}
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-xs min-w-[800px]">
                                                        <thead class="bg-gray-100/50 dark:bg-gray-800/30 border-b border-gray-200 dark:border-gray-700">
                                                            <tr>
                                                                <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Description *</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-14">QTY *</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-24">Type *</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-orange-500 uppercase w-16" x-show="stop.service_type !== 'cube'">LG</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-orange-500 uppercase w-16" x-show="stop.service_type !== 'cube'">WD</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-orange-500 uppercase w-16" x-show="stop.service_type !== 'cube'">HT</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-14" x-show="stop.service_type !== 'cube'">PCS</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-14" x-show="stop.service_type !== 'cube'">LF</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-orange-500 uppercase w-20" x-show="stop.service_type === 'cube'">Cube</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-20">Total WT *</th>
                                                                <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-20">Class</th>
                                                                <th class="px-2 py-2 w-8"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                            <template x-for="(commodity, cIndex) in stop.commodities" :key="cIndex">
                                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                                                    <td class="px-2 py-1.5">
                                                                        <input type="text" x-model="commodity.description" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white" placeholder="Description">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center">
                                                                        <input type="number" x-model="commodity.qty" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center dark:text-white" placeholder="1" min="1">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center">
                                                                        <select x-model="commodity.type" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white dark:bg-gray-900">
                                                                            <option value="skid">Skid</option>
                                                                            <option value="container">Container</option>
                                                                            <option value="pallet">Pallet</option>
                                                                            <option value="crate">Crate</option>
                                                                            <option value="drum">Drum</option>
                                                                            <option value="box">Box</option>
                                                                            <option value="bag">Bag</option>
                                                                            <option value="bundle">Bundle</option>
                                                                            <option value="roll">Roll</option>
                                                                            <option value="loose">Loose</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center" x-show="stop.service_type !== 'cube'">
                                                                        <input type="number" step="0.01" x-model="commodity.length" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center text-orange-600 dark:text-orange-400" :placeholder="stop.measurements === 'in_lbs' ? '0 in' : '0 cm'">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center" x-show="stop.service_type !== 'cube'">
                                                                        <input type="number" step="0.01" x-model="commodity.width" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center text-orange-600 dark:text-orange-400" :placeholder="stop.measurements === 'in_lbs' ? '0 in' : '0 cm'">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center" x-show="stop.service_type !== 'cube'">
                                                                        <input type="number" step="0.01" x-model="commodity.height" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center text-orange-600 dark:text-orange-400" :placeholder="stop.measurements === 'in_lbs' ? '0 in' : '0 cm'">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center" x-show="stop.service_type !== 'cube'">
                                                                        <input type="number" x-model="commodity.pcs" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center dark:text-white" placeholder="PCS">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center" x-show="stop.service_type !== 'cube'">
                                                                        <input type="number" step="0.01" x-model="commodity.lf" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center dark:text-white" placeholder="lf">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center" x-show="stop.service_type === 'cube'">
                                                                        <input type="number" step="0.01" x-model="commodity.cube" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center text-orange-600 dark:text-orange-400" placeholder="0">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center">
                                                                        <input type="number" step="0.01" x-model="commodity.weight" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center dark:text-white" :placeholder="stop.measurements === 'in_lbs' ? '0 lbs' : '0 kg'">
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center">
                                                                        <select x-model="commodity.freight_class" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white dark:bg-gray-900">
                                                                            <option value="">None</option>
                                                                            <option value="50">50</option>
                                                                            <option value="55">55</option>
                                                                            <option value="60">60</option>
                                                                            <option value="65">65</option>
                                                                            <option value="70">70</option>
                                                                            <option value="77.5">77.5</option>
                                                                            <option value="85">85</option>
                                                                            <option value="92.5">92.5</option>
                                                                            <option value="100">100</option>
                                                                            <option value="110">110</option>
                                                                            <option value="125">125</option>
                                                                            <option value="150">150</option>
                                                                            <option value="175">175</option>
                                                                            <option value="200">200</option>
                                                                            <option value="250">250</option>
                                                                            <option value="300">300</option>
                                                                            <option value="400">400</option>
                                                                            <option value="500">500</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center">
                                                                        <button type="button" @click="removeCommodity(stopIndex, cIndex)" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Accessorial Services - Multi-select Dropdown Style --}}
                                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800" x-data="{ accessorialDropdownOpen: false }">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Accessorial Services</label>
                                            
                                            {{-- Selected Accessorials as Badges --}}
                                            <div class="flex flex-wrap gap-2 mb-2" x-show="stop.accessorials.length > 0">
                                                <template x-for="accId in stop.accessorials" :key="accId">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                                                        <span x-text="getAccessorialName(accId)"></span>
                                                        <button type="button" @click="stop.accessorials = stop.accessorials.filter(id => id !== accId)" class="ml-1 text-primary-500 hover:text-primary-700 dark:hover:text-primary-200">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </span>
                                                </template>
                                            </div>

                                            {{-- Dropdown Trigger --}}
                                            <div class="relative max-w-sm">
                                                <button type="button" @click="accessorialDropdownOpen = !accessorialDropdownOpen" class="w-full flex items-center justify-between px-3 py-2 text-xs text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-gray-300 dark:hover:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                                    <span class="text-gray-500 dark:text-gray-400">+ Add accessorial</span>
                                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="accessorialDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>

                                                {{-- Dropdown Panel - Opens upward to avoid overlap with Add Leg button --}}
                                                <div x-show="accessorialDropdownOpen" 
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     @click.outside="accessorialDropdownOpen = false"
                                                     class="absolute z-50 bottom-full mb-1 left-0 w-72 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                                    <div class="p-2">
                                                        @foreach($allAccessorials as $acc)
                                                        <label class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors">
                                                            <input type="checkbox" value="{{ $acc->id }}" x-model="stop.accessorials" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 w-4 h-4">
                                                            <span class="text-xs text-gray-700 dark:text-gray-300">{{ $acc->name }}</span>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Add Leg Button --}}
                <button type="button" @click="addStop()" class="w-full py-5 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl flex flex-col items-center justify-center text-gray-400 hover:text-primary-600 hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all group">
                    <svg class="w-8 h-8 mb-1 text-gray-300 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-bold">Add Next Leg</span>
                    <span class="text-[10px] text-gray-400">Autofills from previous consignee</span>
                </button>
            </div>
        </div>
    </form>

    {{-- Processing Modal --}}
    <div x-cloak x-show="showProcessModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showProcessModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showProcessModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showProcessModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-gray-800">
                
                <div class="bg-white dark:bg-gray-900">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Order Processing & Quoting</h3>
                            <p class="text-xs text-gray-500">Assign manifests and define financial quotes</p>
                        </div>
                        <button @click="showProcessModal = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            {{-- Manifest Assignments --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        Manifest Assignments
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <select x-model="massManifestId" class="text-[10px] py-1 rounded border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white">
                                            <option value="" class="text-gray-500 bg-white dark:bg-gray-800">Apply to all...</option>
                                            @foreach($manifests as $manifest)
                                                <option value="{{ $manifest->id }}" class="text-black dark:text-white bg-white dark:bg-gray-800">{{ $manifest->manifest_number }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" @click="applyMassManifest()" class="text-[10px] bg-primary-50 text-primary-600 px-2 py-1 rounded hover:bg-primary-100 font-bold transition-colors">Apply</button>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-200">Stop</th>
                                                <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-200">Manifest</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <template x-for="(stop, sIdx) in stops" :key="sIdx">
                                                <tr class="bg-white dark:bg-gray-900">
                                                    <td class="px-3 py-3">
                                                        <div class="font-bold text-gray-900 dark:text-white" x-text="'Stop ' + (sIdx + 1)"></div>
                                                        <div class="text-[10px] text-gray-500 dark:text-gray-400" x-text="(stop.shipper.city || '-') + ' → ' + (stop.consignee.city || '-')"></div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <select x-model="stop.manifest_id" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-black dark:text-white focus:ring-primary-500 focus:border-primary-500 transition-shadow">
                                                            <option value="" class="text-gray-500 bg-white dark:bg-gray-800">No Manifest</option>
                                                            @foreach($manifests as $manifest)
                                                                <option value="{{ $manifest->id }}" class="text-black dark:text-white bg-white dark:bg-gray-800">{{ $manifest->manifest_number }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Financial Quote --}}
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Financial Quotes
                                </h4>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Service</label>
                                        <select name="service_id" x-model="quote.service_id" class="mt-1 block w-full text-xs rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                            <option value="">Choose Service...</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-400 uppercase">Est. Start</label>
                                            <input type="date" name="quote_delivery_start" x-model="quote.delivery_start" class="mt-1 block w-full text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-400 uppercase">Est. End</label>
                                            <input type="date" name="quote_delivery_end" x-model="quote.delivery_end" class="mt-1 block w-full text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                        </div>
                                    </div>
                                    
                                    {{-- Quote Rows (Revenue) --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Customer Quote (Revenue)</span>
                                            <button type="button" @click="addQuoteRow('customer')" class="text-[10px] text-primary-600 font-bold">+ Add Line</button>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 overflow-hidden">
                                            <table class="w-full text-[10px]">
                                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-500">
                                                    <tr>
                                                        <th class="p-2 text-left w-24">Type</th>
                                                        <th class="p-2 text-left">Description</th>
                                                        <th class="p-2 text-right w-24">Amount</th>
                                                        <th class="p-2 w-6"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                    <template x-for="(row, idx) in quote.customer_rows" :key="'cust-'+idx">
                                                        <tr class="hover:bg-gray-100/50 dark:hover:bg-gray-700/30 transition-colors">
                                                            <td class="p-2">
                                                                <select x-model="row.type" class="w-full border-0 bg-white dark:bg-gray-800 p-1 rounded text-[10px] text-black dark:text-white focus:ring-2 focus:ring-primary-500 cursor-pointer">
                                                                    <option value="Freight" class="bg-white dark:bg-gray-800 text-black dark:text-white">Freight</option>
                                                                    <option value="Fuel" class="bg-white dark:bg-gray-800 text-black dark:text-white">Fuel</option>
                                                                    <option value="Accessorial" class="bg-white dark:bg-gray-800 text-black dark:text-white">Accessorial</option>
                                                                    <option value="Other" class="bg-white dark:bg-gray-800 text-black dark:text-white">Other</option>
                                                                </select>
                                                            </td>
                                                            <td class="p-2"><input type="text" x-model="row.description" placeholder="Description" class="w-full border-0 bg-transparent p-0 text-[10px] dark:text-white focus:ring-0"></td>
                                                            <td class="p-2 text-right">
                                                                <div class="flex items-center justify-end gap-1">
                                                                    <span class="text-gray-400">$</span>
                                                                    <input type="number" step="0.01" x-model="row.cost" placeholder="0.00" class="w-16 border-0 bg-transparent p-0 text-right text-[10px] font-bold dark:text-white focus:ring-0">
                                                                </div>
                                                            </td>
                                                            <td class="p-2 text-center"><button type="button" @click="quote.customer_rows.splice(idx, 1)" class="text-gray-400 hover:text-red-500 text-lg">&times;</button></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot class="bg-primary-50/50 dark:bg-primary-900/10">
                                                    <tr>
                                                        <td colspan="2" class="p-2 text-right font-bold uppercase text-gray-500">Revenue Total:</td>
                                                        <td class="p-2 text-right font-bold text-primary-600" x-text="'$' + calculateTotal(quote.customer_rows)"></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Carrier Cost Rows --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Carrier Cost (Expenses)</span>
                                            <button type="button" @click="addQuoteRow('carrier')" class="text-[10px] text-orange-600 font-bold">+ Add Line</button>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 overflow-hidden">
                                            <table class="w-full text-[10px]">
                                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-500">
                                                    <tr>
                                                        <th class="p-2 text-left w-24">Type</th>
                                                        <th class="p-2 text-left">Description</th>
                                                        <th class="p-2 text-right w-24">Amount</th>
                                                        <th class="p-2 w-6"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                    <template x-for="(row, idx) in quote.carrier_rows" :key="'carr-'+idx">
                                                        <tr class="hover:bg-gray-100/50 dark:hover:bg-gray-700/30 transition-colors">
                                                            <td class="p-2">
                                                                <select x-model="row.type" class="w-full border-0 bg-white dark:bg-gray-800 p-1 rounded text-[10px] text-black dark:text-white focus:ring-2 focus:ring-primary-500 cursor-pointer">
                                                                    <option value="Freight" class="bg-white dark:bg-gray-800 text-black dark:text-white">Freight</option>
                                                                    <option value="Fuel" class="bg-white dark:bg-gray-800 text-black dark:text-white">Fuel</option>
                                                                    <option value="Accessorial" class="bg-white dark:bg-gray-800 text-black dark:text-white">Accessorial</option>
                                                                    <option value="Other" class="bg-white dark:bg-gray-800 text-black dark:text-white">Other</option>
                                                                </select>
                                                            </td>
                                                            <td class="p-2"><input type="text" x-model="row.description" placeholder="Description" class="w-full border-0 bg-transparent p-0 text-[10px] dark:text-white focus:ring-0"></td>
                                                            <td class="p-2 text-right">
                                                                <div class="flex items-center justify-end gap-1">
                                                                    <span class="text-gray-400">$</span>
                                                                    <input type="number" step="0.01" x-model="row.cost" placeholder="0.00" class="w-16 border-0 bg-transparent p-0 text-right text-[10px] font-bold dark:text-white focus:ring-0">
                                                                </div>
                                                            </td>
                                                            <td class="p-2 text-center"><button type="button" @click="quote.carrier_rows.splice(idx, 1)" class="text-gray-400 hover:text-red-500 text-lg">&times;</button></td>
                                                        </tr>
                                                    </template>
                                                    <template x-if="quote.carrier_rows.length === 0">
                                                        <tr>
                                                            <td colspan="4" class="p-4 text-center text-gray-400 italic">No carrier costs defined.</td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot class="bg-orange-50/50 dark:bg-orange-900/10">
                                                    <tr>
                                                        <td colspan="2" class="p-2 text-right font-bold uppercase text-gray-500">Cost Total:</td>
                                                        <td class="p-2 text-right font-bold text-orange-600" x-text="'$' + calculateTotal(quote.carrier_rows)"></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                        <button type="button" @click="showProcessModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="button" @click="submitForm()" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-lg flex items-center gap-2">
                             <span x-show="submitting">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                             </span>
                             Process & Finalize Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        stops: @json($stopsData),
        quote: @json($quoteData),
        accessorialsList: @json($allAccessorials->pluck('name', 'id')),

        saving: false,
        submitting: false,
        showProcessModal: false,
        massManifestId: '',

        init() {
            if (this.stops.length === 0) {
                this.addStop();
            }
            // Ensure stops have service_type and measurements
            this.stops.forEach(stop => {
                if (!stop.service_type) stop.service_type = 'truckload';
                if (!stop.measurements) stop.measurements = 'in_lbs';
            });
        },

        getAccessorialName(id) {
            return this.accessorialsList[id] || 'Unknown';
        },

        addStop() {
            let autofill = {
                company_name: '', address_1: '', address_2: '', city: '', state: '', zip: '', country: 'US',
                contact_name: '', phone: '', email: '', opening_time: '08:00', closing_time: '17:00',
                ready_date: '', ready_time: '', appointment: false, notes: ''
            };

            if (this.stops.length > 0) {
                const lastStop = this.stops[this.stops.length - 1];
                autofill = { ...lastStop.consignee };
                lastStop.expanded = false;
            }

            this.stops.push({
                uid: Date.now(),
                expanded: true,
                manifest_id: '',
                service_type: 'truckload',
                measurements: 'in_lbs',
                shipper: { ...autofill },
                consignee: { company_name: '', address_1: '', address_2: '', city: '', state: '', zip: '', country: 'US', contact_name: '', phone: '', email: '', opening_time: '08:00', closing_time: '17:00', ready_date: '', ready_time: '', appointment: false, notes: '' },
                billing: { customs_broker: '', port_of_entry: '', container_number: '', declared_value: 0, currency: 'USD', ref_number: '', customer_po_number: '' },
                commodities: [this.newCommodity()],
                accessorials: []
            });
        },

        newCommodity() {
            return {
                description: '',
                qty: 1,
                type: 'skid',
                length: 0,
                width: 0,
                height: 0,
                pcs: 0,
                lf: 0,
                cube: 0,
                weight: 0,
                freight_class: ''
            };
        },

        removeStop(idx) {
            if (this.stops.length > 1) {
                this.stops.splice(idx, 1);
            }
        },

        addCommodity(stopIdx) {
            this.stops[stopIdx].commodities.push(this.newCommodity());
        },

        removeCommodity(stopIdx, cIdx) {
            if (this.stops[stopIdx].commodities.length > 1) {
                this.stops[stopIdx].commodities.splice(cIdx, 1);
            }
        },

        addQuoteRow(type) {
            this.quote[type + '_rows'].push({ type: 'Freight', description: '', cost: 0 });
        },

        applyMassManifest() {
            if (!this.massManifestId) return;
            this.stops.forEach(stop => {
                stop.manifest_id = this.massManifestId;
            });
            {{-- Optional: Show a subtle confirmation --}}
        },

        calculateTotal(rows) {
            return rows.reduce((acc, row) => acc + (parseFloat(row.cost) || 0), 0).toFixed(2);
        },

        calculateProfit() {
            const rev = parseFloat(this.calculateTotal(this.quote.customer_rows));
            const cost = parseFloat(this.calculateTotal(this.quote.carrier_rows));
            return (rev - cost).toFixed(2);
        },

        calculateMargin() {
            const rev = parseFloat(this.calculateTotal(this.quote.customer_rows));
            const cost = parseFloat(this.calculateTotal(this.quote.carrier_rows));
            if (rev === 0) return 0;
            return (((rev - cost) / rev) * 100).toFixed(1);
        },

        saveDraft() {
            this.saving = true;
            document.getElementById('orderForm').submit();
        },

        openConfirmModal() {
            this.showProcessModal = true;
        },

        submitForm() {
            this.submitting = true;
            document.getElementById('orderForm').submit();
        }
    }
}

</script>
@endpush
@endsection



