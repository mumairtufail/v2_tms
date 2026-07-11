@extends('v2.layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header
    title="Dashboard"
    description="Welcome back, {{ auth()->user()->name }}!"
/>

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
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
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
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">${{ number_format($order->total_amount ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    No recent orders found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</x-table-container>
@endsection
