@extends('v2.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="space-y-4">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Activity Logs']]" />

    <!-- Header (No Add button for logs - read only) -->
    <x-page-header title="Activity Logs" description="View all system activity across all companies" />

    <!-- Inline Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('admin.logs') }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <!-- Search -->
            <div class="sm:col-span-6 lg:col-span-8 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user or action..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>

            <!-- Method -->
            <div class="sm:col-span-3 lg:col-span-2">
                <x-filter-select name="method" :value="request('method')" :options="['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE']" placeholder="All Methods" class="w-full" />
            </div>

            <!-- Buttons -->
            <div class="sm:col-span-3 lg:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <button type="submit" class="w-full sm:w-auto flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                @if(request()->hasAny(['search', 'method']))
                <a href="{{ route('admin.logs') }}" class="px-3 py-2 text-sm text-center text-gray-500 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Filters Indicator -->
    @if(request()->hasAny(['search', 'method']))
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-gray-500">Filtering by:</span>
        @if(request('search'))
        <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded">Search: "{{ request('search') }}"</span>
        @endif
        @if(request('method'))
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">Method: {{ request('method') }}</span>
        @endif
    </div>
    @endif

    <!-- Compact Table -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">User</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Method</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Action</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">IP Address</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($activity_logs ?? [] as $index => $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500">{{ $activity_logs->firstItem() + $index }}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-gray-500 rounded-md flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">
                                <x-search-highlight :text="$log->user->name ?? 'System'" :search="request('search')" />
                            </span>
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        @php
                            $methodColors = [
                                'GET' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                'POST' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                'PUT' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                'PATCH' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
                                'DELETE' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                            ];
                            $color = $methodColors[$log->method] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $color }}">{{ $log->method }}</span>
                    </td>
                    <td class="px-3 py-2 text-gray-900 dark:text-white max-w-xs truncate" title="{{ $log->action }}">
                        <x-search-highlight :text="$log->action" :search="request('search')" />
                    </td>
                    <td class="px-3 py-2 hidden md:table-cell">
                        <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-xs text-gray-600 dark:text-gray-400">{{ $log->ip_address ?? 'N/A' }}</code>
                    </td>
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No activity logs found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if(isset($activity_logs) && $activity_logs->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $activity_logs->links() }}</div>
        @endif
    </x-table-container>
</div>
@endsection
