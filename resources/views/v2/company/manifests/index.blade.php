@extends('v2.layouts.app')

@section('title', 'Manifests')

@section('content')
<div class="space-y-6" x-data="{ selected: [], allSelected: false }">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Manifests']]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <x-page-header title="Manifests" description="Manage your shipping manifests" />
        <div class="flex items-center gap-3">
            <template x-if="selected.length > 0">
                <form id="bulk-delete-form" action="{{ route('v2.manifests.bulk-destroy', $company->slug) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete ' + selected.length + ' manifest(s)? This action cannot be undone.');">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 hover:bg-red-200 font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Selected (<span x-text="selected.length"></span>)
                    </button>
                </form>
            </template>
            <button @click="$dispatch('open-modal', 'create-manifest-modal')" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Manifest
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search manifests..." class="block w-full pl-10 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
            <div class="w-full sm:w-48">
                <select name="status" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Filter
            </button>
        </form>
    </div>
    
    <x-filter-tags :filters="['search', 'status']" />

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-medium text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" 
                                @change="allSelected = !allSelected; selected = allSelected ? [{{ $manifests->pluck('id')->implode(', ') }}] : []" 
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="px-6 py-4">Manifest ID</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Start Date</th>
                        <th class="px-6 py-4">Orders</th>
                        <th class="px-6 py-4">Drivers</th>
                        <th class="px-6 py-4">Carriers</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($manifests as $manifest)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $manifest->id }}" x-model="selected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => $manifest->id]) }}" class="hover:text-primary-600 hover:underline">
                                    {{ $manifest->code }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $manifest->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                       ($manifest->status === 'dispatched' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                                       'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                                    {{ ucfirst($manifest->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $manifest->start_date ? \Carbon\Carbon::parse($manifest->start_date)->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($manifest->orders->count() > 0)
                                    <div class="flex flex-col gap-1">
                                        @foreach($manifest->orders as $order)
                                            <a href="{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]) }}" class="text-xs text-primary-600 hover:underline">
                                                {{ $order->order_number }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex -space-x-2 overflow-hidden">
                                    @foreach($manifest->drivers->take(3) as $driver)
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-200 flex items-center justify-center text-xs font-bold" title="{{ $driver->name }}">
                                            {{ substr($driver->name, 0, 1) }}
                                        </div>
                                    @endforeach
                                    @if($manifest->drivers->count() > 3)
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                            +{{ $manifest->drivers->count() - 3 }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ $manifest->carriers->pluck('carrier_name')->join(', ') ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => $manifest->id]) }}" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('v2.manifests.destroy', ['company' => $company->slug, 'manifest' => $manifest->id]) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No manifests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $manifests->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    <x-modal name="create-manifest-modal" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('v2.manifests.store', ['company' => $company->slug]) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create New Manifest</h2>
            
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                     A new manifest will be created with today's date and "Pending" status.
                </p>
                <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Create & Edit Manifest
                </button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
