@props([
    'indexUrl',
    'readAllUrl',
    'readUrlTemplate',
])

<div
    x-data="notificationBell({
        indexUrl: @js($indexUrl),
        readAllUrl: @js($readAllUrl),
        readUrlTemplate: @js($readUrlTemplate),
    })"
    x-init="init()"
    class="relative"
>
    <button
        type="button"
        @click="toggle()"
        class="relative flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
        aria-label="Notifications"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span
            x-show="unreadCount > 0"
            x-text="unreadCount > 9 ? '9+' : unreadCount"
            class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
        ></span>
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-2 z-50"
        x-cloak
        style="display: none;"
    >
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</p>
            <button
                type="button"
                x-show="unreadCount > 0"
                @click="markAllRead()"
                class="text-[11px] font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400"
            >
                Mark all read
            </button>
        </div>

        <div class="max-h-64 overflow-y-auto">
            <template x-if="loading">
                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</div>
            </template>

            <template x-if="!loading && items.length === 0">
                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No notifications yet.</div>
            </template>

            <template x-for="item in items" :key="item.id">
                <a
                    :href="item.url || '#'"
                    @click.prevent="openItem(item)"
                    class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    :class="!item.read_at ? 'bg-primary-50/40 dark:bg-primary-900/10' : ''"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                             :class="iconClasses(item.icon)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.title"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="item.body"></p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1" x-text="item.created_at_human"></p>
                        </div>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function notificationBell(config) {
    return {
        open: false,
        loading: false,
        items: [],
        unreadCount: 0,
        pollTimer: null,
        init() {
            this.load();
            this.pollTimer = setInterval(() => this.load(false), 60000);
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.load();
            }
        },
        iconClasses(icon) {
            const map = {
                order: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
                manifest: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400',
                driver: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
            };
            return map[icon] || 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400';
        },
        async load(showSpinner = true) {
            if (showSpinner) this.loading = true;
            try {
                const response = await fetch(config.indexUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;
                const data = await response.json();
                this.items = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            } catch (e) {
                console.error('Failed to load notifications', e);
            } finally {
                this.loading = false;
            }
        },
        async openItem(item) {
            if (!item.read_at) {
                await this.markRead(item.id, false);
            }
            if (item.url) {
                window.location.href = item.url;
            }
            this.open = false;
        },
        async markRead(id, reload = true) {
            const url = config.readUrlTemplate.replace('__ID__', id);
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (reload) {
                await this.load(false);
            } else {
                const target = this.items.find(i => i.id === id);
                if (target) target.read_at = new Date().toISOString();
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
        },
        async markAllRead() {
            await fetch(config.readAllUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            await this.load(false);
        },
    };
}
</script>
@endpush
@endonce
