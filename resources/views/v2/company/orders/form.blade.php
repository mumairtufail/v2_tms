@extends('v2.layouts.app')

@section('title', isset($order) ? "Edit Order #{$order->order_number}" : "New Order Draft")

@section('content')
<div class="space-y-6" x-data="orderForm()">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Orders', 'url' => route('v2.orders.index', $company)],
        ['label' => isset($order) ? 'Edit Order' : 'New Order']
    ]" />

    <!-- 2. Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('v2.orders.index', $company) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <x-page-header :title="isset($order) ? 'Order #' . $order->order_number : 'New Order Draft'" 
                          :description="'Manage details for ' . ($order->customer->name ?? 'Unknown') . ($order->order_type ? ' (' . str_replace('_', ' ', ucfirst($order->order_type)) . ')' : '')" />
        </div>
        
        <div class="flex items-center gap-2">
            <x-secondary-button @click="saveDraft()" type="button">
                <x-loader size="xs" x-show="saving" class="mr-2" />
                Save as Draft
            </x-secondary-button>
            <x-primary-button @click="openConfirmModal()">
                Submit & Process
            </x-primary-button>
        </div>
    </div>

    <!-- 3. Form Sections Toggle (Optional if tabs needed) -->
    
    <form id="orderForm" action="{{ route('v2.orders.update', ['company' => $company->slug, 'order' => $order->id]) }}" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="order_type" value="{{ $order->order_type }}">
        
        <div class="grid grid-cols-1 gap-6">
            <!-- General Info Section -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Order References</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-form-section title="Basic References" class="md:col-span-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="ref_number" value="Reference Number" />
                                <x-text-input id="ref_number" name="ref_number" type="text" class="mt-1 block w-full" :value="old('ref_number', $order->ref_number)" placeholder="Internal/Customer Ref" />
                            </div>
                            <div>
                                <x-input-label for="customer_po_number" value="Customer PO" />
                                <x-text-input id="customer_po_number" name="customer_po_number" type="text" class="mt-1 block w-full" :value="old('customer_po_number', $order->customer_po_number)" placeholder="PO Number" />
                            </div>
                            <div>
                                <x-input-label for="special_instructions" value="Special Instructions" />
                                <textarea id="special_instructions" name="special_instructions" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm" rows="1">{{ old('special_instructions', $order->special_instructions) }}</textarea>
                            </div>
                        </div>
                    </x-form-section>
                </div>
            </div>

            <!-- Stops Management -->
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

                <!-- Dynamic Stops List -->
                <div class="space-y-6">
                    <template x-for="(stop, index) in stops" :key="stop.uid">
                        <div class="relative">
                            <!-- Connection Line -->
                            <div x-show="index < stops.length - 1" class="absolute left-6 top-12 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-800 z-0"></div>
                            
                            <!-- Stop Card -->
                            <div class="relative z-10 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 transition-all hover:shadow-md overflow-hidden" :class="stop.expanded ? '' : 'group'">
                                <!-- Stop Header (Collapsed) -->
                                <div @click="stop.expanded = !stop.expanded" class="cursor-pointer p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center w-6 h-6 rounded-full bg-primary-600 text-white text-[10px] font-bold" x-text="index + 1"></div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="stop.shipper.company_name || 'Empty Shipper'"></span>
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="stop.consignee.company_name || 'Empty Consignee'"></span>
                                            </div>
                                            <div class="flex items-center gap-1 text-[10px] text-gray-500 mt-0.5">
                                                <span x-text="stop.shipper.city + ', ' + stop.shipper.state"></span>
                                                <span>•</span>
                                                <span x-text="stop.consignee.city + ', ' + stop.consignee.state"></span>
                                                <span class="ml-2 font-medium text-primary-500" x-text="stop.commodities.length + ' items'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click.stop="removeStop(index)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="stop.expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>

                                <!-- Stop Body (Expanded) -->
                                <div x-show="stop.expanded" x-collapse>
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 lg:grid-cols-2 gap-6 bg-gray-50/30 dark:bg-gray-800/10">
                                        
                                        <!-- Shipper Section -->
                                        <div class="space-y-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-2 h-2 rounded-full bg-green-500 ring-4 ring-green-100 dark:ring-green-900/30"></div>
                                                <h4 class="text-xs font-bold text-gray-500 uppercase">Shipper Information (Pickup)</h4>
                                            </div>
                                            @include('v2.company.orders.partials.location-fields', ['prefix' => 'shipper', 'stopKey' => 'index'])
                                        </div>

                                        <!-- Consignee Section -->
                                        <div class="space-y-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-2 h-2 rounded-full bg-blue-500 ring-4 ring-blue-100 dark:ring-blue-900/30"></div>
                                                <h4 class="text-xs font-bold text-gray-500 uppercase">Consignee Information (Delivery)</h4>
                                            </div>
                                            @include('v2.company.orders.partials.location-fields', ['prefix' => 'consignee', 'stopKey' => 'index'])
                                        </div>

                                        <!-- Billing & Additional Info Section -->
                                        <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-2 h-2 rounded-full bg-yellow-500 ring-4 ring-yellow-100 dark:ring-yellow-900/30"></div>
                                                <h4 class="text-xs font-bold text-gray-500 uppercase">Additional & Billing Details</h4>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                                <div>
                                                    <x-input-label value="Customs Broker" class="text-[10px] uppercase text-gray-400" />
                                                    <x-text-input :name="`stops[${index}][customs_broker]`" x-model="stop.billing.customs_broker" class="mt-0.5 block w-full text-xs" />
                                                </div>
                                                <div>
                                                    <x-input-label value="Port of Entry" class="text-[10px] uppercase text-gray-400" />
                                                    <x-text-input :name="`stops[${index}][port_of_entry]`" x-model="stop.billing.port_of_entry" class="mt-0.5 block w-full text-xs" />
                                                </div>
                                                <div>
                                                    <x-input-label value="Container #" class="text-[10px] uppercase text-gray-400" />
                                                    <x-text-input :name="`stops[${index}][container_number]`" x-model="stop.billing.container_number" class="mt-0.5 block w-full text-xs" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                     <div>
                                                        <x-input-label value="Value" class="text-[10px] uppercase text-gray-400" />
                                                        <x-text-input type="number" step="0.01" :name="`stops[${index}][declared_value]`" x-model="stop.billing.declared_value" class="mt-0.5 block w-full text-xs" />
                                                    </div>
                                                    <div>
                                                        <x-input-label value="Curr" class="text-[10px] uppercase text-gray-400" />
                                                        <select :name="`stops[${index}][currency]`" x-model="stop.billing.currency" class="mt-0.5 block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                                            <option value="USD">USD</option>
                                                            <option value="CAD">CAD</option>
                                                            <option value="EUR">EUR</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Commodities & Accessories Section (Full Width) -->
                                        <div class="lg:col-span-2 space-y-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <!-- Commodities Table -->
                                            <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                                                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between border-b border-gray-200 dark:border-gray-800">
                                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Commodity Items</span>
                                                    <button type="button" @click="addCommodity(index)" class="text-xs text-primary-600 hover:text-primary-700 font-bold flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        Add Item
                                                    </button>
                                                </div>
                                                <table class="w-full text-xs">
                                                    <thead class="bg-gray-100/50 dark:bg-gray-800/30">
                                                        <tr>
                                                            <th class="px-3 py-2 text-left text-[10px] text-gray-400 uppercase">Description</th>
                                                            <th class="px-3 py-2 text-left text-[10px] text-gray-400 uppercase w-20">Pieces</th>
                                                            <th class="px-3 py-2 text-left text-[10px] text-gray-400 uppercase w-24">Weight</th>
                                                            <th class="px-3 py-2 text-left text-[10px] text-gray-400 uppercase w-32">Dimensions</th>
                                                            <th class="px-3 py-2 text-right w-10"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                        <template x-for="(commodity, cIndex) in stop.commodities" :key="cIndex">
                                                            <tr>
                                                                <td class="px-3 py-2">
                                                                    <input type="text" x-model="commodity.description" :name="`stops[${index}][commodities][${cIndex}][description]`" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white" placeholder="Item description...">
                                                                </td>
                                                                <td class="px-3 py-2">
                                                                    <input type="number" x-model="commodity.pieces" :name="`stops[${index}][commodities][${cIndex}][pieces]`" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white" placeholder="1">
                                                                </td>
                                                                <td class="px-3 py-2">
                                                                    <input type="number" step="0.01" x-model="commodity.weight" :name="`stops[${index}][commodities][${cIndex}][weight]`" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white" placeholder="0.00">
                                                                </td>
                                                                <td class="px-3 py-2">
                                                                    <input type="text" x-model="commodity.dimensions" :name="`stops[${index}][commodities][${cIndex}][dimensions]`" class="w-full border-0 bg-transparent focus:ring-1 focus:ring-primary-500 rounded p-1 text-xs dark:text-white" placeholder="L x W x H">
                                                                </td>
                                                                <td class="px-3 py-2 text-right">
                                                                    <button type="button" @click="removeCommodity(index, cIndex)" class="text-gray-400 hover:text-red-500">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Accessorials Multi-select -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Accessorial Services</label>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($allAccessorials as $acc)
                                                    <label class="cursor-pointer group">
                                                        <input type="checkbox" :name="`stops[${index}][accessorials][]`" value="{{ $acc->id }}" class="sr-only peer" 
                                                               x-model="stop.accessorials">
                                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/30 peer-checked:text-primary-700 dark:peer-checked:text-primary-300 peer-checked:border-primary-200 dark:peer-checked:border-primary-800 transition-all">
                                                            {{ $acc->name }}
                                                            <svg x-show="stop.accessorials.includes('{{ $acc->id }}')" class="ml-1.5 w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        </span>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Add Leg Button -->
                <button type="button" @click="addStop()" class="w-full py-4 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl flex flex-col items-center justify-center text-gray-400 hover:text-primary-600 hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all group">
                    <svg class="w-6 h-6 mb-1 text-gray-300 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-bold">Add Next Leg</span>
                    <span class="text-[10px]">Autofills from previous consignee</span>
                </button>
            </div>
        </div>
    </form>
</div>

    <!-- 4. Processing & Quoting Modal -->
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
                            <p class="text-xs text-gray-500">Assign manifests and define financial quotes before finalizing</p>
                        </div>
                        <button @click="showProcessModal = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Left: Manifest Assignments -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    Manifest Assignments
                                </h4>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Stop (Leg)</th>
                                                <th class="px-3 py-2 text-left">Assign Manifest</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <template x-for="(stop, index) in stops" :key="index">
                                                <tr>
                                                    <td class="px-3 py-3">
                                                        <div class="font-bold" x-text="'Stop ' + (index + 1)"></div>
                                                        <div class="text-[10px] text-gray-500" x-text="stop.shipper.city + ' → ' + stop.consignee.city"></div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <select :name="`stops[${index}][manifest_id]`" x-model="stop.manifest_id" class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                                            <option value="">No Manifest</option>
                                                            @foreach($manifests as $manifest)
                                                                <option value="{{ $manifest->id }}">{{ $manifest->manifest_number }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Right: Financial Quote -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Financial Quotes
                                </h4>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-input-label value="Assigned Service" class="text-[10px]" />
                                        <select name="service_id" x-model="quote.service_id" class="mt-1 block w-full text-xs rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                            <option value="">Choose Service...</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label value="Est. Start Date" class="text-[10px]" />
                                            <x-text-input type="date" name="quote_delivery_start" x-model="quote.delivery_start" class="mt-1 block w-full text-xs" />
                                        </div>
                                        <div>
                                            <x-input-label value="Est. End Date" class="text-[10px]" />
                                            <x-text-input type="date" name="quote_delivery_end" x-model="quote.delivery_end" class="mt-1 block w-full text-xs" />
                                        </div>
                                    </div>
                                    
                                    <!-- Costs Tables (Simplified for now, can expand later) -->
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Customer Quote (Revenue)</span>
                                            <button type="button" @click="addQuoteRow('customer')" class="text-[10px] text-primary-600 font-bold">+ Add Line</button>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 overflow-hidden">
                                            <table class="w-full text-[10px]">
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                    <template x-for="(row, idx) in quote.customer_rows" :key="idx">
                                                        <tr>
                                                            <td class="p-2"><input type="text" :name="`customer_quotes[${idx}][description]`" x-model="row.description" placeholder="Description" class="w-full border-0 bg-transparent p-0 text-[10px]"></td>
                                                            <td class="p-2 w-20 text-right"><input type="number" step="0.01" :name="`customer_quotes[${idx}][cost]`" x-model="row.cost" placeholder="0.00" class="w-full border-0 bg-transparent p-0 text-right text-[10px] font-bold"></td>
                                                            <td class="p-2 w-6 text-center"><button type="button" @click="quote.customer_rows.splice(idx, 1)" class="text-gray-400 hover:text-red-500">×</button></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot class="bg-primary-50 dark:bg-primary-900/10">
                                                    <tr>
                                                        <td class="p-2 text-right font-bold uppercase">Total Revenue:</td>
                                                        <td class="p-2 text-right font-bold text-primary-600" x-text="'$' + calculateTotal(quote.customer_rows)"></td>
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

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3 rounded-b-xl">
                        <button type="button" @click="showProcessModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="button" @click="submitForm()" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-lg flex items-center gap-2">
                             <x-loader size="xs" x-show="submitting" />
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
        stops: @json($order->stops->map(function($stop) {
            $consignee = json_decode($stop->consignee_data, true) ?? [];
            $billing = json_decode($stop->billing_data, true) ?? [];
            return [
                'uid' => uniqid(),
                'expanded' => true,
                'manifest_id' => $stop->manifest_id ?? '',
                'shipper' => [
                    'company_name' => $stop->company_name,
                    'address_1' => $stop->address_1,
                    'city' => $stop->city,
                    'state' => $stop->state,
                    'zip' => $stop->postal_code,
                    'contact_name' => $stop->contact_name,
                    'phone' => $stop->contact_phone,
                    'opening_time' => $stop->opening_time,
                    'closing_time' => $stop->closing_time,
                    'appointment' => (bool)$stop->is_appointment,
                ],
                'consignee' => $consignee,
                'billing' => array_merge([
                    'customs_broker' => '',
                    'port_of_entry' => '',
                    'container_number' => '',
                    'declared_value' => 0,
                    'currency' => 'USD'
                ], $billing),
                'commodities' => $stop->commodities->count() > 0 ? $stop->commodities->map(fn($c) => [
                    'description' => $c->description,
                    'pieces' => $c->quantity,
                    'weight' => $c->weight,
                    'dimensions' => $c->dimensions,
                ])->toArray() : [['description' => '', 'pieces' => 1, 'weight' => 0, 'dimensions' => '']],
                'accessorials' => $stop->accessorials->pluck('id')->map(fn($id) => (string)$id)->toArray(),
            ];
        })),
        
        quote: {
            service_id: '{{ $order->quote->service_id ?? '' }}',
            delivery_start: '{{ $order->quote->quote_delivery_start ?? '' }}',
            delivery_end: '{{ $order->quote->quote_delivery_end ?? '' }}',
            customer_rows: @json($order->quote && $order->quote->costs ? $order->quote->costs->where('type', 'customer')->map(fn($c) => ['description' => $c->description, 'cost' => $c->amount])->values()->toArray() : [['description' => 'Freight Charge', 'cost' => 0]]),
            carrier_rows: @json($order->quote && $order->quote->costs ? $order->quote->costs->where('type', 'carrier')->map(fn($c) => ['description' => $c->description, 'cost' => $c->amount])->values()->toArray() : [])
        },

        saving: false,
        submitting: false,
        showProcessModal: false,

        init() {
            if (this.stops.length === 0) {
                this.addStop();
            }
        },

        addStop() {
            let autofill = {
                company_name: '', address_1: '', city: '', state: '', zip: '', contact_name: '', phone: '', opening_time: '08:00', closing_time: '17:00'
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
                shipper: { ...autofill },
                consignee: { company_name: '', address_1: '', city: '', state: '', zip: '', contact_name: '', phone: '', opening_time: '08:00', closing_time: '17:00' },
                billing: { customs_broker: '', port_of_entry: '', container_number: '', declared_value: 0, currency: 'USD' },
                commodities: [{ description: '', pieces: 1, weight: 0, dimensions: '' }],
                accessorials: []
            });
        },

        removeStop(index) {
            if (this.stops.length > 1) {
                this.stops.splice(index, 1);
            }
        },

        addCommodity(stopIndex) {
            this.stops[stopIndex].commodities.push({ description: '', pieces: 1, weight: 0, dimensions: '' });
        },

        removeCommodity(stopIndex, cIndex) {
            if (this.stops[stopIndex].commodities.length > 1) {
                this.stops[stopIndex].commodities.splice(cIndex, 1);
            }
        },

        addQuoteRow(type) {
            this.quote[type + '_rows'].push({ description: '', cost: 0 });
        },

        calculateTotal(rows) {
            return rows.reduce((acc, row) => acc + (parseFloat(row.cost) || 0), 0).toFixed(2);
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
