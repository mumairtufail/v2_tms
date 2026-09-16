@extends('v2.layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header
    title="Dashboard"
    description="Welcome back, {{ auth()->user()->name }}!"
/>

{{-- Headline numbers for this company --}}
@php
    $dashCards = [
        [
            'label' => 'Orders',
            'value' => $stats['orders'] ?? 0,
            'note' => ($stats['orders_live'] ?? 0) . ' moving' . (($stats['orders_new'] ?? 0) ? ' · ' . $stats['orders_new'] . ' new this month' : ''),
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'url' => auth()->user()->hasPermission('orders', 'view') ? route('v2.orders.index', ['company' => $company->slug]) : null,
        ],
        [
            'label' => 'Manifests',
            'value' => $stats['manifests'] ?? 0,
            'note' => ($stats['manifests_open'] ?? 0) . ' open',
            'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
            'url' => auth()->user()->hasPermission('manifests', 'view') ? route('v2.manifests.index', ['company' => $company->slug]) : null,
        ],
        [
            'label' => 'Customers',
            'value' => $stats['customers'] ?? 0,
            'note' => ($stats['carriers'] ?? 0) . ' active carriers',
            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'url' => auth()->user()->hasPermission('customers', 'view') ? route('v2.customers.index', ['company' => $company->slug]) : null,
        ],
        [
            'label' => 'Carriers',
            'value' => $stats['carriers'] ?? 0,
            'note' => 'Active partners',
            'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
            'url' => auth()->user()->hasPermission('carriers', 'view') ? route('v2.carriers.index', ['company' => $company->slug]) : null,
        ],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach($dashCards as $card)
    <{{ $card['url'] ? 'a' : 'div' }} @if($card['url']) href="{{ $card['url'] }}" @endif
        class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm p-5 {{ $card['url'] ? 'transition-colors hover:border-primary-300 dark:hover:border-primary-800' : '' }}">
        <div class="flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
            </span>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
        </div>
        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white tabular-nums">{{ number_format($card['value']) }}</p>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 truncate">{{ $card['note'] }}</p>
    </{{ $card['url'] ? 'a' : 'div' }}>
    @endforeach
</div>

<!-- Recent Orders -->
<x-table-container>
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
        @if(auth()->user()->hasPermission('orders', 'view') && app()->bound('current.company'))
        <a href="{{ route('v2.orders.index', ['company' => app('current.company')->slug]) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
            View all
        </a>
        @endif
    </div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order #</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentOrders ?? [] as $order)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                    @if(auth()->user()->hasPermission('orders', 'view') && app()->bound('current.company'))
                    <a href="{{ route('v2.orders.edit', ['company' => app('current.company')->slug, 'order' => $order->id]) }}" class="hover:text-primary-600 dark:hover:text-primary-400">
                        #{{ $order->order_number }}
                    </a>
                    @else
                    #{{ $order->order_number }}
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->customer->name ?? 'N/A' }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    No recent orders found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</x-table-container>
@endsection
