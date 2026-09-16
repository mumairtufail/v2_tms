@extends('v2.layouts.app')

@section('title', 'Customers')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('customers', 'create');
    $canUpdate = auth()->user()->hasPermission('customers', 'update');
    $canDelete = auth()->user()->hasPermission('customers', 'delete');
    $sortable = ['name', 'short_code', 'location_sharing', 'network_customer', 'is_active'];
    $currentSort = in_array(request('sort'), $sortable, true) ? request('sort') : 'name';
    $currentDirection = request('direction') === 'desc' ? 'desc' : 'asc';
    $th = 'px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400';
@endphp

<div class="space-y-4">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Customers']]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Customers" description="Manage your customers, their people, addresses and defaults" />
        @if($canCreate)
        <button type="button" x-data @click="$dispatch('open-modal', 'create-customer')" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Customer
        </button>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('v2.customers.index', ['company' => $company->slug]) }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <input type="hidden" name="sort" value="{{ $currentSort }}">
            <input type="hidden" name="direction" value="{{ $currentDirection }}">

            <!-- Search -->
            <div class="sm:col-span-6 lg:col-span-8 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or short code..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
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
                    <th class="w-12 {{ $th }}">#</th>
                    @foreach([
                        'name' => ['Name', ''],
                        'short_code' => ['Short Code', ''],
                        'portal' => ['Portal', 'hidden md:table-cell'],
                        'location_sharing' => ['Location Sharing', 'hidden lg:table-cell'],
                        'network_customer' => ['Network Customer', 'hidden lg:table-cell'],
                        'is_active' => ['Status', ''],
                    ] as $column => [$label, $responsive])
                    <th class="{{ $th }} {{ $responsive }} whitespace-nowrap">
                        @if(in_array($column, $sortable, true))
                        <a href="{{ route('v2.customers.index', array_merge(request()->except(['page', 'create']), ['company' => $company->slug, 'sort' => $column, 'direction' => ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc'])) }}"
                           class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-white">
                            {{ $label }}
                            @if($currentSort === $column)
                            <svg class="w-3 h-3 {{ $currentDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                            @endif
                        </a>
                        @else
                        {{ $label }}
                        @endif
                    </th>
                    @endforeach
                    <th class="w-24 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($customers as $index => $customer)
                @php $showUrl = route('v2.customers.show', ['company' => $company->slug, 'customer' => $customer->id]); @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500">{{ $customers->firstItem() + $index }}</td>
                    <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">
                        <a href="{{ $showUrl }}" class="flex items-center gap-2.5 min-w-0 hover:text-primary-600 dark:hover:text-primary-400">
                            @if($customer->logoUrl())
                            <img src="{{ $customer->logoUrl() }}" alt="" class="w-7 h-7 rounded-full object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                            @else
                            <span class="w-7 h-7 rounded-full shrink-0 grid place-items-center text-[10px] font-bold text-white" style="background-color: {{ $customer->avatarColor() }}">{{ $customer->initials() }}</span>
                            @endif
                            <span class="truncate"><x-search-highlight :text="$customer->name" :search="request('search')" /></span>
                        </a>
                    </td>
                    <td class="px-3 py-2 text-gray-500 font-mono text-xs">{{ $customer->short_code ?? '-' }}</td>
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">{{ $customer->has_portal_access ? 'Yes' : 'No' }}</td>
                    <td class="px-3 py-2 text-gray-500 hidden lg:table-cell whitespace-nowrap">{{ $customer->locationSharingLabel() }}</td>
                    <td class="px-3 py-2 text-gray-500 hidden lg:table-cell">{{ $customer->network_customer ? 'Yes' : 'No' }}</td>
                    <td class="px-3 py-2">
                        @if($customer->is_active)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ $showUrl }}" class="p-1 text-gray-400 hover:text-primary-600" title="{{ $canUpdate ? 'Open' : 'View' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($canDelete)
                                @if($customer->orders_count > 0)
                                <button type="button" x-data @click="$dispatch('open-modal', 'customer-has-orders-{{ $customer->id }}')" class="p-1 text-gray-300 dark:text-gray-600 hover:text-gray-500" title="Can't delete: this customer has orders">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @else
                                <button type="button" x-data @click="$dispatch('open-modal', 'delete-customer-{{ $customer->id }}')" class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-3 py-6 text-center text-gray-500">No customers found</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($customers->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $customers->links() }}</div>
        @endif
    </x-table-container>
</div>

<!-- Delete Modals -->
@if($canDelete)
@foreach($customers->where('orders_count', 0) as $customer)
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

@foreach($customers->where('orders_count', '>', 0) as $customer)
    @include('v2.company.customers.partials.has-orders-modal', ['return' => 'index'])
@endforeach
@endif

@if($canCreate)
    @include('v2.company.customers.partials.create-modal')
@endif
@endsection
