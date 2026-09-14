{{-- Order activity slide-over. Open from anywhere with $dispatch('open-order-logs', { id, number }) --}}
@php
    $logsTopOffset = session()->has('impersonating_original_id') ? 'top-10' : 'top-0';
@endphp
<div x-data="orderActivityLog(@js(route('v2.orders.activity-logs', ['company' => $company->slug, 'order' => '__ORDER__'])))"
     @open-order-logs.window="show($event.detail.id, $event.detail.number)"
     @keydown.escape.window="isOpen && close()"
     class="contents">
    {{-- Teleported to <body> so a parent's space-y margin or transform can't push the fixed panel down --}}
    <template x-teleport="body">
    <div x-show="isOpen" x-cloak class="fixed inset-x-0 bottom-0 {{ $logsTopOffset }} z-[70]">
    <div class="absolute inset-0 bg-gray-900/30"
         x-show="isOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"></div>

    <aside class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white dark:bg-gray-900 shadow-xl border-l border-gray-200 dark:border-gray-800 flex flex-col"
           x-show="isOpen"
           x-transition:enter="transform transition ease-out duration-200"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full">
        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-800">
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Activity</p>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="orderNumber ? ('Order #' + orderNumber) : 'Order activity'"></h2>
            </div>
            <button type="button" @click="close()" title="Close" class="p-1.5 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-white dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-3">
            <p x-show="loading" class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">Loading activity…</p>
            <p x-show="!loading && logs.length === 0" class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">No activity recorded for this order yet.</p>

            <ol x-show="!loading && logs.length > 0" class="ml-1.5 border-l border-gray-200 dark:border-gray-700">
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

@push('scripts')
<script>
function orderActivityLog(urlTemplate) {
    return {
        isOpen: false,
        loading: false,
        logs: [],
        orderNumber: '',
        requestId: 0,

        async show(orderId, orderNumber) {
            this.isOpen = true;
            this.orderNumber = orderNumber || '';
            this.logs = [];
            this.loading = true;
            document.body.classList.add('overflow-hidden');

            // Ignore late responses if another order's log was opened meanwhile
            const requestId = ++this.requestId;
            try {
                const response = await fetch(urlTemplate.replace('__ORDER__', orderId), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Failed to load logs');
                const data = await response.json();
                if (requestId !== this.requestId) return;
                this.logs = data.logs || [];
                this.orderNumber = data.order_number || this.orderNumber;
            } catch (e) {
                if (requestId === this.requestId) this.logs = [];
            } finally {
                if (requestId === this.requestId) this.loading = false;
            }
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
    };
}
</script>
@endpush
