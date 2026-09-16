@extends('portal.layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="space-y-6" x-data="orderLogsPanel()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Orders</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Track and view your shipment orders</p>
        </div>
        <form method="POST" action="{{ route('portal.orders.store', ['company' => $company->slug]) }}">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Order
            </button>
        </form>
    </div>

    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order #, ref, PO..."
            class="flex-1 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
        <select name="status" class="pl-4 pr-10 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            <option value="">All Statuses</option>
            @foreach(['draft', 'new', 'quoted', 'booked', 'in_transit', 'delivered'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg">Filter</button>
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Manifest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stops</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->order_number }}</div>
                            @if($order->ref_number)
                            <div class="text-xs text-gray-500 dark:text-gray-400">Ref: {{ $order->ref_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $order->order_type_label }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $order->manifest?->code ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $order->stops->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @php
                                // One action per row: drafts open in the editor, everything else read-only
                                $canEditInPortal = $order->status === 'draft' && $order->order_type === 'point_to_point';
                            @endphp
                            <div class="inline-flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="openLogs({{ $order->id }}, {{ json_encode($order->order_number) }})"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:text-primary-400 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
                                        title="Activity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                <a href="{{ $canEditInPortal
                                        ? route('portal.orders.edit', ['company' => $company->slug, 'order' => $order])
                                        : route('portal.orders.show', ['company' => $company->slug, 'order' => $order]) }}"
                                   class="text-primary-600 hover:text-primary-500 dark:text-primary-400 font-medium">{{ $canEditInPortal ? 'Edit' : 'View' }}</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    {{-- Full-height slide-over on the right, matching the company side's activity panel.
         Teleported so the page's own spacing cannot offset it. --}}
    <template x-teleport="body">
    <div x-show="logPanelOpen" x-cloak class="fixed inset-0 z-[70]" @keydown.escape.window="closeLogs()">
        <div class="absolute inset-0 bg-gray-900/30"
             x-show="logPanelOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeLogs()"></div>

        <aside class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white dark:bg-gray-900 shadow-xl border-l border-gray-200 dark:border-gray-800 flex flex-col"
               x-show="logPanelOpen"
               x-transition:enter="transform transition ease-out duration-200"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Activity</p>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="logOrderNumber ? ('Order #' + logOrderNumber) : 'Order activity'"></h2>
                </div>
                <button type="button" @click="closeLogs()" title="Close" class="p-1.5 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-white dark:hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-3">
                <p x-show="logsLoading" class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">Loading activity…</p>
                <p x-show="!logsLoading && logs.length === 0" class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">No activity recorded for this order yet.</p>

                <ol x-show="!logsLoading && logs.length > 0" class="ml-1.5 border-l border-gray-200 dark:border-gray-700">
                    <template x-for="log in logs" :key="log.id">
                        <li class="relative pl-4 pb-3 last:pb-0">
                            <span class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-gray-900"
                                  :class="log.successful ? 'bg-primary-500' : 'bg-red-500'"></span>
                            <p class="text-[13px] leading-5 text-gray-900 dark:text-white">
                                <span class="font-semibold" x-text="log.actor"></span>
                                <span class="text-gray-600 dark:text-gray-300" x-text="log.description"></span>
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500" :title="log.created_at_label" x-text="log.created_at_human"></p>
                        </li>
                    </template>
                </ol>
            </div>
        </aside>
    </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function orderLogsPanel() {
    return {
        logPanelOpen: false,
        logsLoading: false,
        logs: [],
        logOrderNumber: '',
        logsUrlTemplate: @json(route('portal.orders.activity-logs', ['company' => $company->slug, 'order' => '__ORDER__'])),
        async openLogs(orderId, orderNumber) {
            this.logPanelOpen = true;
            this.logOrderNumber = orderNumber;
            this.logs = [];
            this.logsLoading = true;
            document.body.classList.add('overflow-hidden');
            try {
                const url = this.logsUrlTemplate.replace('__ORDER__', orderId);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Failed to load logs');
                const data = await response.json();
                this.logs = data.logs || [];
                this.logOrderNumber = data.order_number || orderNumber;
            } catch (e) {
                this.logs = [];
            } finally {
                this.logsLoading = false;
            }
        },
        closeLogs() {
            this.logPanelOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
    };
}
</script>
@endpush
