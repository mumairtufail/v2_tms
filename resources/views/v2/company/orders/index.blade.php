@extends('v2.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="space-y-4" x-data="{}">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Orders']]" />

    <!-- 2. Header with Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Orders" description="Manage and track all transport orders" />
        <button @click="$dispatch('open-modal', 'create-order')" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Order
        </button>
    </div>

    <!-- 3. Inline Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 shadow-sm">
        <form action="{{ route('v2.orders.index', $company) }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <!-- Search Input -->
            <div class="sm:col-span-6 lg:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, Customer, Ref #..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Filter Dropdowns -->
            <div class="sm:col-span-3 lg:col-span-2">
                <x-filter-select name="status" :value="request('status')" 
                    :options="[
                        'new' => 'New', 
                        'draft' => 'Draft', 
                        'active' => 'Active', 
                        'dispatched' => 'Dispatched', 
                        'completed' => 'Completed', 
                        'invoiced' => 'Invoiced',
                        'cancelled' => 'Cancelled'
                    ]" 
                    placeholder="All Status" class="w-full" />
            </div>
            
            <!-- Buttons -->
            <div class="sm:col-span-3 lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('v2.orders.index', $company) }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Active Filters Indicator -->
    @if(request()->hasAny(['search', 'status']))
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-gray-500">Filtering by:</span>
        @if(request('search'))
        <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 rounded border border-primary-200 dark:border-primary-800 flex items-center gap-1">
            Search: "{{ request('search') }}"
        </span>
        @endif
        @if(request('status'))
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-700">
            Status: {{ ucfirst(request('status')) }}
        </span>
        @endif
    </div>
    @endif

    <!-- 5. Orders Table -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Stops</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="w-24 px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($orders as $index => $order)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors group">
                    <td class="px-4 py-2 text-gray-500 align-top">{{ $orders->firstItem() + $index }}</td>
                    
                    <td class="px-4 py-2 align-top">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">
                                <x-search-highlight :text="$order->order_number" :search="request('search')" />
                            </span>
                            <span class="text-xs text-gray-500 mt-0.5">Type: {{ str_replace('_', ' ', ucfirst($order->order_type)) }}</span>
                            @if($order->ref_number)
                                <span class="text-xs text-gray-400 mt-0.5">Ref: {{ $order->ref_number }}</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-4 py-2 align-top">
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900 dark:text-white">
                                <x-search-highlight :text="$order->customer->name ?? 'N/A'" :search="request('search')" />
                            </span>
                            @if($order->customer_po_number)
                                <span class="text-xs text-gray-500">PO: {{ $order->customer_po_number }}</span>
                            @endif
                        </div>
                    </td>
                    
                    <td class="px-4 py-2 align-top hidden lg:table-cell">
                        <div class="flex flex-col gap-1">
                            @if($order->stops->count() > 0)
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $order->stops->first()->city }}, {{ $order->stops->first()->state }}</span>
                                </div>
                                @if($order->stops->count() > 1)
                                <div class="w-px h-2 ml-1 bg-gray-300 dark:bg-gray-700"></div>
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $order->stops->last()->city }}, {{ $order->stops->last()->state }}</span>
                                </div>
                                <div class="text-[10px] text-primary-500 font-medium ml-4 mt-1">+{{ $order->stops->count() - 2 }} intermediate stops</div>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs font-italic">No stops defined</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-4 py-2 align-top">
                        @php
                            $statusClasses = [
                                'new' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700',
                                'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800',
                                'completed' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
                            ];
                            $class = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full border {{ $class }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    
                    <td class="px-4 py-2 align-top text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]) }}" class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-all" title="Edit Order">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button" @click="$dispatch('open-modal', 'delete-order-{{ $order->id }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-gray-500 font-medium">No orders found</p>
                            <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
            {{ $orders->links() }}
        </div>
        @endif
    </x-table-container>

    <!-- 6. Create Order Modal -->
    <x-confirm-modal name="create-order" title="Create New Order">
        <div x-data="{ 
            selectedCustomer: null,
            searchCustomer: '',
            customers: [],
            loadingCustomers: false,
            
            async fetchCustomers() {
                this.loadingCustomers = true;
                const res = await fetch(`{{ route('v2.orders.search-customers', ['company' => $company->slug]) }}?q=${this.searchCustomer}`);
                this.customers = await res.json();
                this.loadingCustomers = false;
            },

            init() {
                // Initial fetch when opened
                this.$watch('$store.modal.active', value => {
                    if (value === 'create-order' && this.customers.length === 0) {
                        this.fetchCustomers();
                    }
                });
            }
        }" @open-modal.window="if($event.detail === 'create-order') fetchCustomers()">
            
            <form action="{{ route('v2.orders.store', $company) }}" method="POST" id="createOrderForm">
                @csrf
                <input type="hidden" name="order_type" value="sequence">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Customer</label>
                        <div class="relative">
                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" x-model="searchCustomer" @input.debounce.500ms="fetchCustomers()" 
                                placeholder="Search customer by name..." 
                                class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:text-white transition-all">
                            
                            <div x-show="loadingCustomers" class="absolute right-3 top-2.5">
                                <x-loader size="sm" />
                            </div>
                        </div>
                        
                        <!-- Customer List (Scrollable) -->
                        <div class="mt-2 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-800/50">
                            <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="customer in customers" :key="customer.id">
                                    <button type="button" @click="selectedCustomer = customer; searchCustomer = customer.name" 
                                        :class="selectedCustomer?.id === customer.id ? 'bg-primary-50 dark:bg-primary-900/40 ring-1 ring-inset ring-primary-500' : 'hover:bg-white dark:hover:bg-gray-800'"
                                        class="w-full text-left px-4 py-3 text-sm transition-colors flex items-center justify-between group">
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white" x-text="customer.name"></div>
                                            <div class="text-[10px] text-gray-500" x-text="`${customer.address}, ${customer.city}, ${customer.state}`"></div>
                                        </div>
                                        <div x-show="selectedCustomer?.id === customer.id" class="text-primary-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </button>
                                </template>
                                <template x-if="customers.length === 0 && !loadingCustomers">
                                    <div class="p-4 text-center text-gray-500 text-xs">No customers found</div>
                                </template>
                            </div>
                        </div>
                        <input type="hidden" name="customer_id" :value="selectedCustomer?.id">
                    </div>

                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800/50">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                <p class="font-bold mb-1">Voyage Type: Sequence</p>
                                <p>This will create a multi-stop order draft. You can add more legs in the next step.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footer">
            <button type="button" @click="$dispatch('close-modal', 'create-order')" 
                class="px-4 py-2 text-sm font-medium bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">Cancel</button>
            <button type="submit" form="createOrderForm" :disabled="!selectedCustomer"
                class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                Create Draft
            </button>
        </x-slot>
    </x-confirm-modal>

    <!-- 7. Delete Modal -->
    @foreach($orders as $order)
    <x-confirm-modal name="delete-order-{{ $order->id }}" title="Delete Order">
        <div class="space-y-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">Are you sure you want to delete order <strong>{{ $order->order_number }}</strong>?</p>
            <p class="text-xs text-red-500 font-medium">This action cannot be undone and will delete all associated stops and data.</p>
        </div>
        <x-slot name="footer">
            <button type="button" @click="$dispatch('close-modal', 'delete-order-{{ $order->id }}')" class="px-4 py-2 text-sm font-medium bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">Cancel</button>
            <form action="{{ route('v2.orders.destroy', ['company' => $company->slug, 'order' => $order->id]) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors shadow-sm">Confirm Delete</button>
            </form>
        </x-slot>
    </x-confirm-modal>
    @endforeach
</div>
@endsection
