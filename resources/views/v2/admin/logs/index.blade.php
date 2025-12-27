@extends('v2.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Activity Logs']
    ]" />

    <!-- Page Header -->
    <x-page-header title="Activity Logs" description="View all system activity across all companies" />

    <!-- Search & Filters -->
    <x-search-filters :route="route('admin.logs')" placeholder="Search by user or action...">
        <x-slot name="filters">
            <select name="method" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                <option value="">All Methods</option>
                <option value="GET" {{ request('method') === 'GET' ? 'selected' : '' }}>GET</option>
                <option value="POST" {{ request('method') === 'POST' ? 'selected' : '' }}>POST</option>
                <option value="PUT" {{ request('method') === 'PUT' ? 'selected' : '' }}>PUT</option>
                <option value="DELETE" {{ request('method') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
            </select>
        </x-slot>
    </x-search-filters>

    <!-- Active Search Indicator -->
    @if(request('search'))
    <x-search-active :term="request('search')" :clearRoute="route('admin.logs')" />
    @endif

    <!-- Logs Table -->
    <x-table-container>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($activity_logs ?? [] as $key => $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $activity_logs->firstItem() + $key }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($log->user->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    <x-search-highlight :text="$log->user->name ?? 'System'" :search="request('search')" />
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $methodColors = [
                                    'GET' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                    'POST' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                    'PUT' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                    'PATCH' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                                    'DELETE' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                ];
                                $color = $methodColors[$log->method] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-400';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">{{ $log->method }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate" title="{{ $log->action }}">
                            <x-search-highlight :text="$log->action" :search="request('search')" />
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-xs">{{ $log->ip_address ?? 'N/A' }}</code>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">No activity logs found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($activity_logs) && $activity_logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
            {{ $activity_logs->links() }}
        </div>
        @endif
    </x-table-container>
</div>
@endsection
