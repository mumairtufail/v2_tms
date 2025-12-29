@extends('v2.layouts.app')

@section('title', 'Customers')

@section('content')
<div class="space-y-4">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Customers']]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Customers" description="Manage your customers" />
        @if(auth()->user()->hasPermission('customers', 'create'))
        <a href="{{ route('v2.customers.create', ['company' => $company->slug]) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Customer
        </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('v2.customers.index', ['company' => $company->slug]) }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <!-- Search -->
            <div class="sm:col-span-6 lg:col-span-8 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <!-- Status -->
            <div class="sm:col-span-3 lg:col-span-2">
                <x-filter-select name="status" :value="request('status')" :options="['active' => 'Active', 'inactive' => 'Inactive']" placeholder="All Status" class="w-full" />
            </div>
            
            <!-- Buttons -->
            <div class="sm:col-span-3 lg:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <button type="submit" class="w-full sm:w-auto flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('v2.customers.index', ['company' => $company->slug]) }}" class="px-3 py-2 text-sm text-center text-gray-500 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Filters -->
    <x-filter-tags :filters="['search', 'status']" />

    <!-- Table -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">Email</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">City/State</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">QuickBooks</th>
                    <th class="w-24 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($customers as $index => $customer)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500">{{ $customers->firstItem() + $index }}</td>
                    <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">
                        <x-search-highlight :text="$customer->name" :search="request('search')" />
                        @if($customer->short_code)
                        <span class="text-xs text-gray-500 block">{{ $customer->short_code }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">{{ $customer->customer_email ?? '-' }}</td>
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">
                        @if($customer->city || $customer->state)
                            {{ $customer->city }}{{ $customer->city && $customer->state ? ', ' : '' }}{{ $customer->state }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($customer->is_active)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($customer->quickbooks_id)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400" title="ID: {{ $customer->quickbooks_id }}">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            Synced
                        </span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">Not Synced</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            @if(!$customer->quickbooks_id && auth()->user()->hasPermission('customers', 'update'))
                            <form action="{{ route('v2.customers.sync-quickbooks', ['company' => $company->slug, 'customer' => $customer->id]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1 text-gray-400 hover:text-blue-600" title="Sync to QuickBooks">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->hasPermission('customers', 'update'))
                            <a href="{{ route('v2.customers.edit', ['company' => $company->slug, 'customer' => $customer->id]) }}" class="p-1 text-gray-400 hover:text-primary-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                            @if(auth()->user()->hasPermission('customers', 'delete'))
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-customer-{{ $customer->id }}')" class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">No customers found</td></tr>
                @endforelse
            </tbody>
        </table>
        
        @if($customers->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $customers->links() }}</div>
        @endif
    </x-table-container>
</div>

<!-- Delete Modals -->
@foreach($customers as $customer)
<x-confirm-modal name="delete-customer-{{ $customer->id }}" title="Delete Customer">
    <p class="text-sm text-gray-600 dark:text-gray-400">Delete <strong>{{ $customer->name }}</strong>?</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-customer-{{ $customer->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
        <form action="{{ route('v2.customers.destroy', ['company' => $company->slug, 'customer' => $customer->id]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
@endforeach
@endsection
