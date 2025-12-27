@extends('v2.layouts.app')

@section('title', 'Carriers')

@section('content')
<div class="space-y-4">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Carriers']]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Carriers" description="Manage your carriers" />
        <a href="{{ route('v2.carriers.create', ['company' => $company->slug]) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Carrier
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('v2.carriers.index', ['company' => $company->slug]) }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <!-- Search -->
            <div class="sm:col-span-6 lg:col-span-8 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search carriers..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <!-- Status -->
            <div class="sm:col-span-3 lg:col-span-2">
                <x-filter-select name="status" :value="request('status')" :options="['active' => 'Active', 'inactive' => 'Inactive']" placeholder="All Status" class="w-full" />
            </div>
            
            <!-- Buttons -->
            <div class="sm:col-span-3 lg:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <button type="submit" class="w-full sm:w-auto flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('v2.carriers.index', ['company' => $company->slug]) }}" class="px-3 py-2 text-sm text-center text-gray-500 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">Clear</a>
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
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">DOT/Docket</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">City/State</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="w-24 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($carriers as $index => $carrier)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500">{{ $carriers->firstItem() + $index }}</td>
                    <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">
                        <x-search-highlight :text="$carrier->carrier_name" :search="request('search')" />
                    </td>
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">
                        @if($carrier->dot_id) DOT: {{ $carrier->dot_id }} @endif
                        @if($carrier->docket_number) <br> MC: {{ $carrier->docket_number }} @endif
                    </td>
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">
                        @if($carrier->city || $carrier->state)
                            {{ $carrier->city }}{{ $carrier->city && $carrier->state ? ', ' : '' }}{{ $carrier->state }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($carrier->is_active)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('v2.carriers.edit', ['company' => $company->slug, 'carrier' => $carrier->id]) }}" class="p-1 text-gray-400 hover:text-primary-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-carrier-{{ $carrier->id }}')" class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No carriers found</td></tr>
                @endforelse
            </tbody>
        </table>
        
        @if($carriers->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $carriers->links() }}</div>
        @endif
    </x-table-container>
</div>

<!-- Delete Modals -->
@foreach($carriers as $carrier)
<x-confirm-modal name="delete-carrier-{{ $carrier->id }}" title="Delete Carrier">
    <p class="text-sm text-gray-600 dark:text-gray-400">Delete <strong>{{ $carrier->carrier_name }}</strong>?</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-carrier-{{ $carrier->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
        <form action="{{ route('v2.carriers.destroy', ['company' => $company->slug, 'carrier' => $carrier->id]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
@endforeach
@endsection
