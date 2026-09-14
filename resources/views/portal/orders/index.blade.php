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
                            <div class="inline-flex items-center justify-end gap-2">
                                @if($order->status === 'draft')
                                <a href="{{ route('portal.orders.edit', ['company' => $company->slug, 'order' => $order]) }}"
                                   class="text-primary-600 hover:text-primary-500 dark:text-primary-400 font-medium">Edit</a>
                                @endif
                                <button type="button"
                                        @click="openLogs({{ $order->id }}, {{ json_encode($order->order_number) }})"
                                        class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-all"
                                        title="View activity logs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                <a href="{{ route('portal.orders.show', ['company' => $company->slug, 'order' => $order]) }}"
                                   class="text-primary-600 hover:text-primary-500 dark:text-primary-400 font-medium">View</a>
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

    <div x-show="logPanelOpen" class="fixed inset-0 z-50" x-cloak @keydown.escape.window="closeLogs()">
        <div class="absolute inset-0 transition-opacity"
             style="background-color: rgba(17, 24, 39, 0.15);"
             x-show="logPanelOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeLogs()"></div>

        {{-- Compact floating card anchored top-right (inline sizing: these values aren't in the prebuilt CSS) --}}
        <aside class="absolute right-4 w-full max-w-sm bg-white dark:bg-gray-900 shadow-2xl rounded-2xl border border-gray-200 dark:border-gray-800 flex flex-col overflow-hidden"
               style="top: 5rem; max-height: calc(100vh - 7rem); width: calc(100% - 2rem);"
               x-show="logPanelOpen"
               x-transition:enter="transform transition ease-out duration-200"
               x-transition:enter-start="translate-x-full opacity-0"
               x-transition:enter-end="translate-x-0 opacity-100"
               x-transition:leave="transform transition ease-in duration-150"
               x-transition:leave-start="translate-x-0 opacity-100"
               x-transition:leave-end="translate-x-full opacity-0"
               @click.stop>
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Order activity</p>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mt-0.5" x-text="logOrderNumber ? ('Order #' + logOrderNumber) : 'Order logs'"></h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Who added, updated, or changed this order</p>
                </div>
                <button type="button" @click="closeLogs()" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-white dark:hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-5">
                <template x-if="logsLoading">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-10">Loading activity...</p>
                </template>

                <template x-if="!logsLoading && logs.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-10">No activity recorded for this order yet.</p>
                </template>

                <ol class="relative border-l border-gray-200 dark:border-gray-700 ml-3" x-show="!logsLoading && logs.length > 0">
                    <template x-for="log in logs" :key="log.id">
                        <li class="mb-6 ml-6">
                            <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full ring-4 ring-white dark:ring-gray-900"
                                  :class="log.successful ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <p class="text-sm text-gray-900 dark:text-white">
                                <span class="font-semibold" x-text="log.actor"></span>
                                <span class="text-gray-600 dark:text-gray-300" x-text="' ' + log.description"></span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1" x-text="log.created_at_label + ' · ' + log.created_at_human"></p>
                        </li>
                    </template>
                </ol>
            </div>
        </aside>
    </div>
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
