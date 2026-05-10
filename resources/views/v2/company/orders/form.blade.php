@extends('v2.layouts.app')

@section('title', isset($order) ? "Edit Order #{$order->order_number}" : "New Order Draft")

@section('content')
<script>
    window.googleMapsApiKey = @json(config('services.google.maps_api_key'));
</script>
<div class="space-y-6" x-data="orderForm()">
    {{-- 1. Breadcrumb --}}
    <x-v2-breadcrumb :items="[
        ['label' => 'Orders', 'url' => route('v2.orders.index', $company)],
        ['label' => isset($order) ? 'Edit Order' : 'New Order']
    ]" />

    {{-- 2. Page Header with Actions --}}
    @php
        $statusClasses = [
            'draft' => 'bg-gray-100 text-gray-700 border-gray-300',
            'new' => 'bg-blue-100 text-blue-700 border-blue-300',
            'no_quote' => 'bg-amber-100 text-amber-700 border-amber-300',
            'quoted' => 'bg-indigo-100 text-indigo-700 border-indigo-300',
            'booked' => 'bg-green-100 text-green-700 border-green-300',
            'in_transit' => 'bg-cyan-100 text-cyan-700 border-cyan-300',
            'delivered' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
            'invoiced' => 'bg-purple-100 text-purple-700 border-purple-300',
            'paid' => 'bg-teal-100 text-teal-700 border-teal-300',
        ];
        $statusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-700 border-gray-300';
    @endphp
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('v2.orders.index', $company) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <x-page-header :title="isset($order) ? 'Order #' . $order->order_number : 'New Order Draft'" 
                          :description="'Manage details for ' . ($order->customer->name ?? 'Unknown')" />
        </div>
        
        <div class="flex items-center gap-2">
            <x-secondary-button x-show="isDraftOrder()" @click="saveDraft()" type="button">
                <span x-show="saving" class="mr-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </span>
                Save as Draft
            </x-secondary-button>
            <x-primary-button @click="primaryAction()">
                <span x-text="isDraftOrder() ? 'Save & Submit' : 'Submit to Quote'"></span>
            </x-primary-button>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 mt-1">
        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold rounded-full border {{ $statusClass }}">
            Current Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}
        </span>
        <a href="{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline">
            Order ID: {{ $order->order_number }}
        </a>
        @if($order->customer)
            <a href="{{ route('v2.customers.edit', ['company' => $company->slug, 'customer' => $order->customer->id]) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline">
                Customer: {{ $order->customer->name }}
            </a>
        @endif
    </div>

    {{-- 3. Order Type Selection (Modern Radio Style) --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h3 class="text-[11px] font-bold text-gray-900 dark:text-white uppercase tracking-widest">Select Strategy</h3>
                <p class="text-[10px] text-gray-400">Choose the move type for this order</p>
            </div>
            <div class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-tighter dark:bg-emerald-900/20">
                Mode: {{ str_replace('_', ' ', $order->order_type) }}
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $orderTypes = [
                    'point_to_point' => [
                        'label' => 'Origin-to-Destination',
                        'desc' => '1 Pickup / 1 Drop'
                    ],
                    'single_shipper' => [
                        'label' => 'Multi-Destination',
                        'desc' => '1 Origin / Multi-Drop'
                    ],
                    'single_consignee' => [
                        'label' => 'Milk Run',
                        'desc' => 'Multi-Pickup / 1 Drop'
                    ],
                    'sequence' => [
                        'label' => 'Shuttle Loop',
                        'desc' => 'Sequential Chained Legs'
                    ],
                ];
            @endphp

            @foreach($orderTypes as $typeKey => $typeData)
                @php 
                    $isSelected = $order->order_type === $typeKey; 
                    $isLocked = !in_array($typeKey, ['sequence']); // Sequence is Shuttle Loop
                @endphp
                <label 
                    @if($isLocked)
                        @click.prevent="window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'This strategy is currently under development.', type: 'info' }}))"
                    @endif
                    class="relative flex flex-col rounded-xl border-2 p-4 transition-all duration-300 group
                    {{ $isLocked ? 'cursor-not-allowed opacity-75 bg-gray-50/50 grayscale-[0.5]' : 'cursor-pointer' }}
                    {{ $isSelected 
                        ? 'bg-emerald-500 border-emerald-600 shadow-[0_4px_20px_rgba(16,185,129,0.25)]' 
                        : ($isLocked ? 'border-gray-200' : 'bg-white border-gray-100 hover:border-emerald-200 hover:bg-gray-50/50 dark:bg-gray-900 dark:border-gray-800') }}">
                    
                    <input type="radio" name="order_type_selector" value="{{ $typeKey }}" class="sr-only" 
                        {{ $isSelected ? 'checked' : '' }}
                        {{ $isLocked ? 'disabled' : '' }}
                        @if(!$isLocked)
                            onchange="window.location.href='{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]) }}?type={{ $typeKey }}'"
                        @endif>
                    
                    <div class="flex items-center justify-between mb-3">
                        {{-- Circular Radio --}}
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-300 
                            {{ $isSelected ? 'border-white bg-white' : ($isLocked ? 'border-gray-200 bg-gray-100' : 'border-gray-300 bg-white group-hover:border-emerald-400') }}">
                            @if($isSelected)
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            @elseif($isLocked)
                                <svg class="w-2.5 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            @endif
                        </div>
                        
                        @if($isSelected)
                            <div class="bg-white/20 rounded-full p-1">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif($isLocked)
                             <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400 flex items-center gap-1">
                                <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                                Coming Soon
                             </span>
                        @endif
                    </div>

                    <div class="mt-auto">
                        <span class="block text-[12px] font-bold leading-tight {{ $isSelected ? 'text-white' : 'text-gray-900 dark:text-gray-100' }}">
                            {{ $typeData['label'] }}
                        </span>
                        <span class="block text-[10px] mt-1 font-medium {{ $isSelected ? 'text-emerald-50' : 'text-gray-400' }}">
                            {{ $isLocked ? 'Feature Locked' : $typeData['desc'] }}
                        </span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    {{-- 4. Main Form --}}
    <form id="orderForm" action="{{ route('v2.orders.update', ['company' => $company->slug, 'order' => $order->id]) }}" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="order_type" value="{{ $order->order_type }}">
        <input type="hidden" name="save_as_draft" x-bind:value="saving ? '1' : '0'">
        <input type="hidden" name="submission_mode" x-bind:value="submissionMode">
        <input type="hidden" name="stops" x-bind:value="JSON.stringify(stops)">
        <input type="hidden" name="quote_data" x-bind:value="JSON.stringify(quote)">
        
        {{-- Validation error banner --}}
        <div id="form-error-banner"
             x-show="formErrors.length > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-4 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-4"
             style="display:none">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-red-700 dark:text-red-400">Please fix the following errors before submitting:</p>
                    <ul class="mt-1 space-y-0.5 list-disc list-inside">
                        <template x-for="err in formErrors" :key="err">
                            <li class="text-xs text-red-600 dark:text-red-400" x-text="err"></li>
                        </template>
                    </ul>
                </div>
                <button type="button" @click="formErrors = []" class="text-red-400 hover:text-red-600 dark:hover:text-red-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            {{-- General Info Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Order References</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Reference Number</label>
                        <input type="text" name="ref_number" value="{{ old('ref_number', $order->ref_number) }}" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="Internal/Customer Ref">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Customer PO</label>
                        <input type="text" name="customer_po_number" value="{{ old('customer_po_number', $order->customer_po_number) }}" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="PO Number">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Container Number</label>
                        <input type="text" name="container_number" x-model="topContainerNumber" @input="onTopContainerInput()" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="e.g. MSCU1234567">
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-400 uppercase">Special Instructions</label>
                        <input type="text" name="special_instructions" value="{{ old('special_instructions', $order->special_instructions) }}" class="mt-1 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md" placeholder="Special instructions for this order">
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
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Legs (Stops)</h3>
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded text-xs" x-text="stops.length + ' Legs'"></span>
                    </div>
                </div>

                {{-- Stops Summary Table (Rose Rocket style) --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Stop #</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase"
                                    x-text="orderType === 'single_consignee' ? 'Pickup (Shipper)' : 'Shipper'"></th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase"
                                    x-text="orderType === 'single_shipper' ? 'Delivery (Consignee)' : 'Consignee'"></th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Manifest</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Items</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(stop, stopIndex) in stops" :key="stop.uid">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer" @click="stop.expanded = !stop.expanded">
                                    <td class="px-3 py-2">
                                        <a :href="'#leg-' + stop.uid" @click.stop="stop.expanded = true" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-[10px] font-bold hover:bg-primary-200 dark:hover:bg-primary-900/50" x-text="stopIndex + 1"></a>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="stop.shipper.company_name || '-'"></div>
                                        <div class="text-[10px] text-gray-400" x-show="stop.shipper.city" x-text="stop.shipper.city + ', ' + stop.shipper.state"></div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="stop.consignee.company_name || '-'"></div>
                                        <div class="text-[10px] text-gray-400" x-show="stop.consignee.city" x-text="stop.consignee.city + ', ' + stop.consignee.state"></div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <template x-if="stop.manifest_id && manifestsMap[stop.manifest_id]">
                                            <a :href="manifestEditUrl(stop.manifest_id)" @click.stop class="text-primary-600 hover:text-primary-700 hover:underline" x-text="manifestsMap[stop.manifest_id]"></a>
                                        </template>
                                        <template x-if="!(stop.manifest_id && manifestsMap[stop.manifest_id])">
                                            <span>-</span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2" x-text="stop.commodities.length"></td>

                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Dynamic Stops List (Expanded Details) --}}
                <div class="space-y-6">
                    <template x-for="(stop, stopIndex) in stops" :key="stop.uid">
                        <div class="relative" :id="'leg-' + stop.uid">
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
                                                    {{-- Locked badge for single_shipper stops after first --}}
                                                    <template x-if="orderType === 'single_shipper' && stopIndex > 0">
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                            Shared
                                                        </span>
                                                    </template>
                                                </div>
                                                <div class="relative">
                                                    <div x-show="orderType === 'single_shipper' && stopIndex > 0"
                                                         class="absolute inset-0 z-10 rounded-lg cursor-not-allowed"
                                                         title="Shared shipper — edit on Stop 1"></div>
                                                    <div :class="(orderType === 'single_shipper' && stopIndex > 0) ? 'opacity-50 pointer-events-none select-none' : ''">
                                                        @include('v2.company.orders.partials.location-fields', ['prefix' => 'shipper'])
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Consignee Section --}}
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full bg-blue-500 ring-4 ring-blue-100 dark:ring-blue-900/30"></div>
                                                    <h4 class="text-xs font-bold text-gray-500 uppercase">Consignee Information (Delivery)</h4>
                                                    {{-- Locked badge for single_consignee stops after first --}}
                                                    <template x-if="orderType === 'single_consignee' && stopIndex > 0">
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                            Shared
                                                        </span>
                                                    </template>
                                                </div>
                                                <div class="relative">
                                                    <div x-show="orderType === 'single_consignee' && stopIndex > 0"
                                                         class="absolute inset-0 z-10 rounded-lg cursor-not-allowed"
                                                         title="Shared consignee — edit on Stop 1"></div>
                                                    <div :class="(orderType === 'single_consignee' && stopIndex > 0) ? 'opacity-50 pointer-events-none select-none' : ''">
                                                        @include('v2.company.orders.partials.location-fields', ['prefix' => 'consignee'])
                                                    </div>
                                                </div>
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
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Container Number <span class="text-red-500">*</span></label>
                                                        <input type="text" x-model="stop.billing.container_number" placeholder="Container Number (required)"
                                                               :readonly="Boolean(topContainerNumber && topContainerNumber.trim())"
                                                               :class="stop._containerError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-gray-700'"
                                                               class="block w-full text-sm dark:bg-gray-800 dark:text-gray-300 rounded-md">
                                                        <p x-show="topContainerNumber && topContainerNumber.trim()" class="text-[10px] text-gray-500 mt-1">Using top-level container number.</p>
                                                        <p x-show="stop._containerError" class="text-red-500 text-[10px] mt-1">Container number is required for each leg.</p>
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

                                        {{-- ── Special Instructions (Required) ── --}}
                                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-2 h-2 rounded-full bg-purple-500 ring-4 ring-purple-100 dark:ring-purple-900/30"></div>
                                                <h4 class="text-xs font-bold text-gray-500 uppercase">
                                                    Special Instructions
                                                    <span class="text-red-500 ml-0.5">*</span>
                                                    <span class="ml-2 text-[9px] font-normal text-gray-400 normal-case">Required for each leg</span>
                                                </h4>
                                            </div>
                                            <textarea
                                                x-model="stop.special_instructions"
                                                rows="3"
                                                placeholder="Enter any special handling, pickup, or delivery instructions for this leg…"
                                                :class="stop._siError
                                                    ? 'border-red-400 focus:border-red-500 focus:ring-red-400 bg-red-50 dark:bg-red-900/10'
                                                    : 'border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500'"
                                                @input="stop._siError = false"
                                                class="block w-full text-sm rounded-md dark:bg-gray-800 dark:text-gray-300 transition-colors resize-none"
                                            ></textarea>
                                            <p x-show="stop._siError" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                Special instructions are required for this leg.
                                            </p>
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
                                                                        <input type="number" x-model="commodity.qty" 
                                                                            @focus="$event.target.select()"
                                                                            class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs text-center dark:text-white" placeholder="1" min="1">
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

                {{-- Add Leg Button (hidden for point_to_point) --}}
                <button type="button" @click="addStop()"
                        x-show="orderType !== 'point_to_point'"
                        class="w-full py-5 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl flex flex-col items-center justify-center text-gray-400 hover:text-primary-600 hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all group">
                    <svg class="w-8 h-8 mb-1 text-gray-300 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-bold"
                          x-text="orderType === 'single_shipper' ? 'Add Delivery Stop' : orderType === 'single_consignee' ? 'Add Pickup Stop' : 'Add Next Leg'"></span>
                    <span class="text-[10px] text-gray-400"
                          x-text="orderType === 'single_shipper' ? 'Shared shipper auto-filled' : orderType === 'single_consignee' ? 'Shared consignee auto-filled' : 'Autofills from previous consignee'"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Processing Modal --}}
            @include('v2.company.orders.partials.quote-modal')


</div>

@push('scripts')
<script>
function orderForm() {
    return {
        stops: @json($stopsData),
        quote: @json($quoteData),
        manifests: @json($manifests),
        manifestsMap: @json($manifestsMap ?? []),
        accessorialsList: @json($allAccessorials->pluck('name', 'id')),

        creatingManifest: false,

        saving: false,
        submitting: false,
        showProcessModal: false,
        massManifestId: '',
        submissionMode: 'new',
        formErrors: [],
        orderStatus: @json($order->status),
        orderType: @json($order->order_type),
        topContainerNumber: @json(old('container_number', $order->container_number ?? '')),

        init() {
            if (this.stops.length === 0) {
                this.addStop();
            }

            this.manifests = (this.manifests || []).map(manifest => ({
                ...manifest,
                id: String(manifest.id),
            }));

            this.stops = this.stops.map(stop => ({
                ...stop,
                manifest_id: stop.manifest_id ? String(stop.manifest_id) : '',
                special_instructions: stop.special_instructions ?? '',
            }));

            // ── Initialize Quote Rows ─────────────────────────────────────────
            // Always guarantee (at minimum) a Freight row [0] and a Fuel row [1].
            // This repairs orders that were saved before the 2-row default existed.
            const ensureDefaultRows = (rows, freightDesc, fuelDesc) => {
                // Stamp is_default on saved rows first
                let r = rows.map((row, i) => ({ ...row, is_default: i < 2 }));

                // Row 0: must be a Freight-type row
                if (r.length === 0 || (r[0].type !== 'Freight' && r[0].type !== 'Freight (per mile)')) {
                    r.unshift({ type: 'Freight', description: freightDesc, qty: 1, rate: 0, cost: 0, is_default: true });
                } else if (!r[0].qty) {
                    r[0].qty = 1;
                }

                // Row 1: must be a Fuel-type row
                const fuelTypes = ['fuel (surcharge)', 'fuel (per mile)', 'fuel (flat)'];
                const row1Type = String(r[1]?.type || '').toLowerCase();
                if (r.length < 2 || !fuelTypes.includes(row1Type)) {
                    r.splice(1, 0, { type: 'Fuel (surcharge)', description: fuelDesc, qty: 0, rate: 0, cost: 0, is_default: true });
                }

                // Re-stamp is_default after possible inserts
                return r.map((row, i) => ({ ...row, is_default: i < 2 }));
            };

            this.quote.customer_rows = ensureDefaultRows(
                this.quote.customer_rows,
                'Freight Charge',
                'Fuel Surcharge (0%)'
            );

            this.quote.carrier_rows = ensureDefaultRows(
                this.quote.carrier_rows,
                'Partner Freight',
                'Fuel Surcharge (0%)'
            );

            // Run normalizeQuoteRows so fuel surcharge rate/description are synced on load
            this.normalizeQuoteRows(this.quote.customer_rows);
            this.normalizeQuoteRows(this.quote.carrier_rows);

            this.quote.delivery_start = this.toDateTimeLocal(this.quote.delivery_start);
            this.quote.delivery_end = this.toDateTimeLocal(this.quote.delivery_end);

            // Ensure stops have service_type, measurements, and error flags
            this.stops.forEach(stop => {
                if (!stop.service_type) stop.service_type = 'truckload';
                if (!stop.measurements) stop.measurements = 'in_lbs';
                if (stop._containerError === undefined) stop._containerError = false;
                if (stop._siError === undefined) stop._siError = false;
                if (stop._readyEndError === undefined) stop._readyEndError = '';
                if (stop._requestedStartError === undefined) stop._requestedStartError = '';
                if (stop._requestedEndError === undefined) stop._requestedEndError = '';

                ['shipper', 'consignee'].forEach(role => {
                    if (!stop[role].ready_at && (stop[role].ready_date || stop[role].ready_time)) {
                        const legacyDate = stop[role].ready_date || '';
                        const legacyTime = stop[role].ready_time || '00:00';
                        if (legacyDate) {
                            const parts = legacyDate.split('-');
                            if (parts.length === 3) {
                                const yy = parts[0].slice(-2);
                                stop[role].ready_at = `${parts[1]}/${parts[2]}/${yy} ${legacyTime.slice(0, 5)}`;
                            }
                        }
                    }

                    if (role === 'shipper') {
                        if (!stop[role].ready_start_at && stop[role].ready_at) stop[role].ready_start_at = stop[role].ready_at;
                        if (!stop[role].ready_end_at && stop[role].ready_at) stop[role].ready_end_at = stop[role].ready_at;

                        stop[role].ready_start_at_picker = this.toDateTimeLocal(stop[role].ready_start_at);
                        stop[role].ready_end_at_picker = this.toDateTimeLocal(stop[role].ready_end_at);
                    } else {
                        if (!stop[role].requested_start_at && stop[role].ready_at) stop[role].requested_start_at = stop[role].ready_at;
                        if (!stop[role].requested_end_at && stop[role].ready_at) stop[role].requested_end_at = stop[role].ready_at;

                        stop[role].requested_start_at_picker = this.toDateTimeLocal(stop[role].requested_start_at);
                        stop[role].requested_end_at_picker = this.toDateTimeLocal(stop[role].requested_end_at);
                    }
                });
            });

            const assignedManifestIds = [...new Set(this.stops.map(stop => stop.manifest_id).filter(Boolean))];
            if (assignedManifestIds.length === 1) {
                this.massManifestId = assignedManifestIds[0];
            }

            if (this.topContainerNumber && this.topContainerNumber.trim()) {
                this.applyTopContainerNumberToAllStops();
            }

            // Live-sync shared side when stop 1 is edited
            if (this.orderType === 'single_shipper') {
                this.$watch('stops[0].shipper', (val) => {
                    this.stops.forEach((stop, i) => { if (i > 0) stop.shipper = { ...val }; });
                }, { deep: true });
            } else if (this.orderType === 'single_consignee') {
                this.$watch('stops[0].consignee', (val) => {
                    this.stops.forEach((stop, i) => { if (i > 0) stop.consignee = { ...val }; });
                }, { deep: true });
            }
        },

        getAccessorialName(id) {
            return this.accessorialsList[id] || 'Unknown';
        },

        toDateTimeLocal(value) {
            if (!value) return '';

            const direct = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;
            if (direct.test(value)) return value;

            const display = /^(\d{2})\/(\d{2})\/(\d{2})\s(\d{2}):(\d{2})$/;
            const displayMatch = String(value).match(display);
            if (displayMatch) {
                const [, mm, dd, yy, hh, min] = displayMatch;
                return `20${yy}-${mm}-${dd}T${hh}:${min}`;
            }

            const spaced = /^(\d{4}-\d{2}-\d{2})\s(\d{2}:\d{2})(:\d{2})?$/;
            const spacedMatch = String(value).match(spaced);
            if (spacedMatch) {
                return `${spacedMatch[1]}T${spacedMatch[2]}`;
            }

            const dateObj = new Date(value);
            if (!Number.isNaN(dateObj.getTime())) {
                const y = dateObj.getFullYear();
                const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(dateObj.getDate()).padStart(2, '0');
                const h = String(dateObj.getHours()).padStart(2, '0');
                const i = String(dateObj.getMinutes()).padStart(2, '0');
                return `${y}-${m}-${d}T${h}:${i}`;
            }

            return '';
        },

        fromDateTimeLocal(value) {
            if (!value) return '';

            const [datePart, timePart = '00:00'] = String(value).split('T');
            const parts = datePart.split('-');
            if (parts.length !== 3) return '';

            const yy = parts[0].slice(-2);
            const mm = parts[1];
            const dd = parts[2];
            return `${mm}/${dd}/${yy} ${timePart.slice(0, 5)}`;
        },

        syncStopDateTime(stop, role, bound) {
            const isStart = bound === 'start';

            if (role === 'shipper') {
                const pickerField = isStart ? 'ready_start_at_picker' : 'ready_end_at_picker';
                const valueField = isStart ? 'ready_start_at' : 'ready_end_at';
                stop[role][valueField] = this.fromDateTimeLocal(stop[role][pickerField]);
                if (isStart) {
                    stop[role].ready_at = stop[role][valueField];
                }
            } else {
                const pickerField = isStart ? 'requested_start_at_picker' : 'requested_end_at_picker';
                const valueField = isStart ? 'requested_start_at' : 'requested_end_at';
                stop[role][valueField] = this.fromDateTimeLocal(stop[role][pickerField]);
                if (!isStart) {
                    stop[role].ready_at = stop[role][valueField];
                }
            }

            // Validate dates in real-time and refresh banner
            this.validateStopDates(stop);
            // Rebuild banner errors from all stops so banner stays accurate
            const errs = [];
            this.stops.forEach((s, i) => {
                if (s._readyEndError) errs.push(`Stop ${i + 1}: ${s._readyEndError}`);
                if (s._requestedStartError) errs.push(`Stop ${i + 1}: ${s._requestedStartError}`);
                if (s._requestedEndError) errs.push(`Stop ${i + 1}: ${s._requestedEndError}`);
            });
            if (this.formErrors.length > 0) this.formErrors = errs;
        },

        parseDateValue(val) {
            if (!val) return null;
            // Handle both picker format (YYYY-MM-DDTHH:mm) and stored format (MM/DD/YY HH:mm)
            const iso = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(val);
            if (iso) return new Date(val);
            const stored = /^(\d{2})\/(\d{2})\/(\d{2})\s(\d{2}):(\d{2})$/.exec(val);
            if (stored) return new Date(`20${stored[3]}-${stored[1]}-${stored[2]}T${stored[4]}:${stored[5]}`);
            const d = new Date(val);
            return isNaN(d.getTime()) ? null : d;
        },

        validateStopDates(stop) {
            // Clear previous errors
            stop._readyEndError = '';
            stop._requestedStartError = '';
            stop._requestedEndError = '';

            const readyStart = this.parseDateValue(stop.shipper.ready_start_at_picker || stop.shipper.ready_start_at);
            const readyEnd   = this.parseDateValue(stop.shipper.ready_end_at_picker   || stop.shipper.ready_end_at);
            const reqStart   = this.parseDateValue(stop.consignee.requested_start_at_picker || stop.consignee.requested_start_at);
            const reqEnd     = this.parseDateValue(stop.consignee.requested_end_at_picker   || stop.consignee.requested_end_at);

            // Ready End must be >= Ready Start
            if (readyStart && readyEnd && readyEnd < readyStart) {
                stop._readyEndError = 'Pickup end must be on or after the pickup start.';
            }

            // Requested Start must be >= Ready Start (can't request delivery before pickup is ready)
            if (readyStart && reqStart && reqStart < readyStart) {
                stop._requestedStartError = 'Delivery start cannot be before the pickup start date.';
            }

            // Requested End must be >= Requested Start
            if (reqStart && reqEnd && reqEnd < reqStart) {
                stop._requestedEndError = 'Delivery end must be on or after the delivery start.';
            }

            // Requested End must be >= Ready End
            if (readyEnd && reqEnd && reqEnd < readyEnd) {
                stop._requestedEndError = stop._requestedEndError || 'Delivery end cannot be before the pickup end date.';
            }
        },

        validateAllDates() {
            let valid = true;
            const errors = [];
            this.stops.forEach((stop, idx) => {
                this.validateStopDates(stop);
                if (stop._readyEndError) {
                    stop.expanded = true;
                    errors.push(`Stop ${idx + 1}: ${stop._readyEndError}`);
                    valid = false;
                }
                if (stop._requestedStartError) {
                    stop.expanded = true;
                    errors.push(`Stop ${idx + 1}: ${stop._requestedStartError}`);
                    valid = false;
                }
                if (stop._requestedEndError) {
                    stop.expanded = true;
                    errors.push(`Stop ${idx + 1}: ${stop._requestedEndError}`);
                    valid = false;
                }
            });
            this.formErrors = errors;
            return valid;
        },

        blankConsignee() {
            return { company_name: '', address_1: '', address_2: '', city: '', state: '', zip: '', country: 'US', lat: null, lng: null, contact_name: '', phone: '', email: '', opening_time: '08:00', closing_time: '17:00', ready_at: '', requested_start_at: '', requested_end_at: '', requested_start_at_picker: '', requested_end_at_picker: '', appointment: false, notes: '' };
        },

        blankShipper() {
            return { company_name: '', address_1: '', address_2: '', city: '', state: '', zip: '', country: 'US', lat: null, lng: null, contact_name: '', phone: '', email: '', opening_time: '08:00', closing_time: '17:00', ready_at: '', ready_start_at: '', ready_end_at: '', ready_start_at_picker: '', ready_end_at_picker: '', appointment: false, notes: '' };
        },

        addStop() {
            let shipperData = this.blankShipper();
            let consigneeData = this.blankConsignee();

            if (this.stops.length > 0) {
                const lastStop = this.stops[this.stops.length - 1];
                lastStop.expanded = false;

                if (this.orderType === 'sequence') {
                    // Sequence: new shipper autofills from previous consignee
                    shipperData = { ...lastStop.consignee };
                } else if (this.orderType === 'single_shipper') {
                    // Shared shipper: always copy stop 1's shipper
                    shipperData = { ...this.stops[0].shipper };
                } else if (this.orderType === 'single_consignee') {
                    // Shared consignee: always copy stop 1's consignee
                    consigneeData = { ...this.stops[0].consignee };
                } else {
                    // point_to_point: only 1 stop allowed, should not reach here
                    shipperData = { ...lastStop.consignee };
                }
            }

            this.stops.push({
                uid: Date.now(),
                expanded: true,
                manifest_id: '',
                service_type: 'truckload',
                measurements: 'in_lbs',
                special_instructions: '',
                shipper: shipperData,
                consignee: consigneeData,
                _containerError: false,
                _readyEndError: '',
                _requestedStartError: '',
                _requestedEndError: '',
                billing: { customs_broker: '', port_of_entry: '', container_number: this.topContainerNumber || '', declared_value: 0, currency: 'USD', ref_number: '', customer_po_number: '' },
                commodities: [this.newCommodity()],
                accessorials: []
            });

            if (this.topContainerNumber && this.topContainerNumber.trim()) {
                this.applyTopContainerNumberToAllStops();
            }
        },

        onTopContainerInput() {
            if (!this.topContainerNumber || this.topContainerNumber.trim() === '') {
                return;
            }

            this.applyTopContainerNumberToAllStops();
        },

        applyTopContainerNumberToAllStops() {
            const sharedValue = (this.topContainerNumber || '').trim();
            if (!sharedValue) return;

            this.stops = this.stops.map(stop => ({
                ...stop,
                _containerError: false,
                billing: {
                    ...stop.billing,
                    container_number: sharedValue,
                }
            }));
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
            this.quote[type + '_rows'].push({ 
                type: 'Miscellaneous', 
                description: '', 
                qty: 1, 
                rate: 0, 
                cost: 0,
                is_default: false 
            });
        },

        applyMassManifest() {
            if (!this.massManifestId) {
                // If checking for "Apply to all..." which is empty
                return;
            }
            
            console.log('Applying manifest ID:', this.massManifestId);
            
            // Normalize target ID to ensure it matches the types in the options
            const targetId = String(this.massManifestId);
            
            // Use map to create a new array, forcing Alpine to react
            this.stops = this.stops.map(stop => {
                // Return a new object to ensure deep reactivity
                return { ...stop, manifest_id: targetId };
            });
        },

      
calculateRowAmount(row, rows) {
    const rowType = String(row.type || '').toLowerCase();
    
    // Logic: Fuel (surcharge) is linked to Freight subtotal
    if (rowType === 'fuel (surcharge)') {
        const percentage = parseFloat(row.qty) || 0;
        const freightBase = this.freightSubtotal(rows);
        return freightBase * (percentage / 100);
    }

    const qty = parseFloat(row.qty) || 0;
    const rate = parseFloat(row.rate) || 0;
    return qty * rate;
},

        calculateTotal(rows) {
            return rows.reduce((acc, row) => acc + this.calculateRowAmount(row, rows), 0).toFixed(2);
        },

        formatMoney(value) {
            return (parseFloat(value) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

     normalizeQuoteRows(rows) {
    const freightBase = this.freightSubtotal(rows);
    rows.forEach(row => {
        const rowType = String(row.type || '').toLowerCase();
        
        if (rowType === 'fuel (surcharge)') {
            // Mirror Freight Cost to the Rate field (for display)
            row.rate = freightBase.toFixed(2);
            // Dynamic description showing %
            row.description = `Fuel Surcharge (${row.qty || 0}%)`;
            row.cost = (freightBase * ((parseFloat(row.qty) || 0) / 100)).toFixed(2);
        } else {
            row.cost = ((parseFloat(row.qty) || 0) * (parseFloat(row.rate) || 0)).toFixed(2);
        }
    });
},

        canDeleteQuoteRow(row) {
            return !row.is_default;
        },
calculateProfit() {
    const revenue = parseFloat(this.calculateTotal(this.quote.customer_rows)) || 0;
    const expenses = parseFloat(this.calculateTotal(this.quote.carrier_rows)) || 0;
    return (revenue - expenses).toFixed(2);
},

calculateMargin() {
    const revenue = parseFloat(this.calculateTotal(this.quote.customer_rows)) || 0;
    const profit = parseFloat(this.calculateProfit()) || 0;
    if (revenue === 0) return 0;
    return ((profit / revenue) * 100).toFixed(1);
},
     duplicateQuoteRow(tableType, index) {
    const rows = tableType === 'customer' ? this.quote.customer_rows : this.quote.carrier_rows;
    const rowToCopy = rows[index];
    const newRow = JSON.parse(JSON.stringify(rowToCopy));
    newRow.is_default = false; // Duplicates are never protected
    rows.splice(index + 1, 0, newRow);
    this.normalizeQuoteRows(rows);
},

        handleQtyInput(rows) {
            this.normalizeQuoteRows(rows);
        },

        addQuoteRow(type) {
            this.quote[type + '_rows'].push({ 
                type: 'Miscellaneous', 
                description: '', 
                qty: 1, 
                rate: 0, 
                cost: 0,
                is_default: false 
            });
        },

     
freightSubtotal(rows) {
    return rows.reduce((acc, row) => {
        const type = String(row.type || '').toLowerCase();
        // sum up all freight-related row costs
        if (type === 'freight' || type === 'freight (per mile)') {
            return acc + (parseFloat(row.qty || 0) * parseFloat(row.rate || 0));
        }
        return acc;
    }, 0);
},

        saveDraft() {
            // Validate date logic even for drafts
            this.stops.forEach(stop => {
                if (stop.shipper.ready_start_at_picker) this.syncStopDateTime(stop, 'shipper', 'start');
                if (stop.shipper.ready_end_at_picker) this.syncStopDateTime(stop, 'shipper', 'end');
                if (stop.consignee.requested_start_at_picker) this.syncStopDateTime(stop, 'consignee', 'start');
                if (stop.consignee.requested_end_at_picker) this.syncStopDateTime(stop, 'consignee', 'end');
            });
            if (!this.validateAllDates()) {
                setTimeout(() => {
                    const errBanner = document.getElementById('form-error-banner');
                    if (errBanner) { errBanner.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
                    const candidates = [...document.querySelectorAll('[data-date-error]')];
                    const visible = candidates.find(el => el.offsetParent !== null && el.textContent.trim());
                    if (visible) visible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150);
                return;
            }
            this.saving = true;
            this.submissionMode = 'draft';
            this.flushFormInputs();
            document.getElementById('orderForm').submit();
        },

        isDraftOrder() {
            return this.orderStatus === 'draft';
        },

        primaryAction() {
            if (this.isDraftOrder()) {
                this.submitAsNew();
                return;
            }

            this.openConfirmModal();
        },

        submitAsNew() {
            this.submissionMode = 'new';
            this.flushFormInputs();
            document.getElementById('orderForm').submit();
        },

        // ── Manually write Alpine state into hidden form inputs ─────────────────
        // Alpine's x-bind:value updates are async (batched). If we call
        // form.submit() synchronously after mutating data (e.g. setting
        // manifest_id on stops), the hidden inputs may still hold the old
        // values. This helper forces them in sync before submission.
        flushFormInputs() {
            const form = document.getElementById('orderForm');
            if (!form) return;
            const set = (name, value) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el) el.value = value;
            };
            set('stops',           JSON.stringify(this.stops));
            set('quote_data',      JSON.stringify(this.quote));
            set('submission_mode', this.submissionMode);
            set('save_as_draft',   this.saving ? '1' : '0');
        },

        openConfirmModal() {
            this.showProcessModal = true;
        },

        async submitQuote() {
            const isValid = this.prepareQuoteSubmission();
            if (!isValid) {
                return;
            }

            const targetManifestIds = [...new Set([
                this.massManifestId,
                ...this.stops.map(stop => stop.manifest_id)
            ].filter(Boolean))];

            if (targetManifestIds.length > 0 && this.quote.carrier_rows.length > 0) {
                await this.syncCarrierCostsToManifests(targetManifestIds);
            }

            this.flushFormInputs();
            document.getElementById('orderForm').submit();
        },

        prepareQuoteSubmission() {
            this.submitting = true;
            this.submissionMode = 'quote';

            this.normalizeQuoteRows(this.quote.customer_rows);
            this.normalizeQuoteRows(this.quote.carrier_rows);

            this.stops.forEach(stop => {
                if (stop.shipper.ready_start_at_picker) this.syncStopDateTime(stop, 'shipper', 'start');
                if (stop.shipper.ready_end_at_picker) this.syncStopDateTime(stop, 'shipper', 'end');
                if (stop.consignee.requested_start_at_picker) this.syncStopDateTime(stop, 'consignee', 'start');
                if (stop.consignee.requested_end_at_picker) this.syncStopDateTime(stop, 'consignee', 'end');
            });

            if (this.topContainerNumber && this.topContainerNumber.trim()) {
                this.applyTopContainerNumberToAllStops();
            }

            // ── Validate required fields ────────────────────────────────────
            let formValid = true;
            const siErrors = [];

            this.stops.forEach((stop, idx) => {
                stop._containerError = false;
                stop._siError = !stop.special_instructions || !stop.special_instructions.trim();
                if (stop._siError) {
                    stop.expanded = true;
                    siErrors.push(`Stop ${idx + 1}: Special instructions are required.`);
                    formValid = false;
                }
            });

            // ── Validate date logic ─────────────────────────────────────────
            if (!this.validateAllDates()) {
                formValid = false;
            }

            this.formErrors = [...siErrors, ...this.formErrors];

            if (!formValid) {
                this.submitting = false;
                // Wait for Alpine to re-render error elements then scroll to first visible one
                setTimeout(() => {
                    const errBanner = document.getElementById('form-error-banner');
                    if (errBanner) {
                        errBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                    // Fallback: find first visible date-error or red-border element
                    const candidates = [...document.querySelectorAll('[data-date-error], .border-red-400')];
                    const visible = candidates.find(el => el.offsetParent !== null && el.textContent.trim());
                    if (visible) visible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150);
                return false;
            }

            return true;
        },

        async syncCarrierCostsToManifests(targetManifestIds) {
            try {
                const urlTemplate = '{{ route('v2.manifests.cost-estimates.store', ['company' => $company->slug, 'manifest' => '__MANIFEST__']) }}';
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const payloadRows = this.quote.carrier_rows.map(row => {
                    const rowType = String(row.type || '').toLowerCase();
                    return {
                        type: row.type,
                        description: row.description,
                        cost: parseFloat(row.cost) || 0,
                        percentage: rowType === 'fuel' ? (parseFloat(row.percentage) || 0) : null,
                    };
                });

                const requests = targetManifestIds.map(manifestId => {
                    const url = urlTemplate.replace('__MANIFEST__', manifestId);
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ cost_estimates: payloadRows })
                    }).then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to sync costs to manifest ' + manifestId);
                        }
                    });
                });

                await Promise.all(requests);
            } catch (e) {
                console.error('Error syncing manifest costs:', e);
            }
        },

        createPendingManifest() {
            this.creatingManifest = true;
            fetch('{{ route("v2.manifests.quick-create", $company->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.manifests.push(data.manifest);
                    // Ensure manifestsMap is reactive
                    this.manifestsMap = { ...this.manifestsMap, [data.manifest.id]: data.manifest.code };
                    
                    this.massManifestId = String(data.manifest.id);
                    
                    // Auto-assign to all stops
                    this.applyMassManifest();
                } else {
                    alert('Error creating manifest: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the manifest.');
            })
            .finally(() => {
                this.creatingManifest = false;
            });
        },

        manifestEditUrl(manifestId) {
            if (!manifestId) return '#';
            return '{{ route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => '__MANIFEST__']) }}'.replace('__MANIFEST__', manifestId);
        }
    }
}

</script>
@endpush
@endsection



