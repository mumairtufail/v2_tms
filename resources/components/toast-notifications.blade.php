<!-- Toast Notifications Component (SheafUI Style) -->
<div 
    x-data="{ 
        toasts: [],
        add(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:toast.window="add($event.detail.type, $event.detail.message)"
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
>
    <!-- Initial Session Toasts -->
    @if(session('success'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        class="pointer-events-auto bg-white dark:bg-gray-900 border border-success-200 dark:border-success-800 rounded-xl shadow-2xl shadow-success-500/10 overflow-hidden"
    >
        <div class="flex items-start gap-3 p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-success-400 to-success-500 rounded-lg flex items-center justify-center shadow-lg shadow-success-500/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Success!</p>
                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="h-1 bg-gradient-to-r from-success-400 to-success-500 animate-[shrink_5s_linear_forwards]" style="animation: shrink 5s linear forwards;"></div>
    </div>
    @endif

    @if(session('error'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        class="pointer-events-auto bg-white dark:bg-gray-900 border border-danger-200 dark:border-danger-800 rounded-xl shadow-2xl shadow-danger-500/10 overflow-hidden"
    >
        <div class="flex items-start gap-3 p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-danger-400 to-danger-500 rounded-lg flex items-center justify-center shadow-lg shadow-danger-500/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Error!</p>
                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="h-1 bg-gradient-to-r from-danger-400 to-danger-500" style="animation: shrink 5s linear forwards;"></div>
    </div>
    @endif

    @if(session('warning'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        class="pointer-events-auto bg-white dark:bg-gray-900 border border-warning-200 dark:border-warning-800 rounded-xl shadow-2xl shadow-warning-500/10 overflow-hidden"
    >
        <div class="flex items-start gap-3 p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-warning-400 to-warning-500 rounded-lg flex items-center justify-center shadow-lg shadow-warning-500/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Warning!</p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ session('warning') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="h-1 bg-gradient-to-r from-warning-400 to-warning-500" style="animation: shrink 5s linear forwards;"></div>
    </div>
    @endif

    @if(session('info'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        class="pointer-events-auto bg-white dark:bg-gray-900 border border-primary-200 dark:border-primary-800 rounded-2xl shadow-2xl shadow-primary-500/10 overflow-hidden"
    >
        <div class="flex items-start gap-4 p-4">
            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Info</p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ session('info') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="h-1 bg-gradient-to-r from-primary-400 to-primary-500" style="animation: shrink 5s linear forwards;"></div>
    </div>
    @endif

    <!-- Dynamic Toasts -->
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full"
            class="pointer-events-auto bg-white dark:bg-gray-900 border rounded-2xl shadow-2xl overflow-hidden"
            :class="{
                'border-success-200 dark:border-success-800 shadow-success-500/10': toast.type === 'success',
                'border-danger-200 dark:border-danger-800 shadow-danger-500/10': toast.type === 'error',
                'border-warning-200 dark:border-warning-800 shadow-warning-500/10': toast.type === 'warning',
                'border-primary-200 dark:border-primary-800 shadow-primary-500/10': toast.type === 'info'
            }"
        >
            <div class="flex items-start gap-4 p-4">
                <div 
                    class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center shadow-lg"
                    :class="{
                        'bg-gradient-to-br from-success-400 to-success-500 shadow-success-500/30': toast.type === 'success',
                        'bg-gradient-to-br from-danger-400 to-danger-500 shadow-danger-500/30': toast.type === 'error',
                        'bg-gradient-to-br from-warning-400 to-warning-500 shadow-warning-500/30': toast.type === 'warning',
                        'bg-gradient-to-br from-primary-400 to-primary-500 shadow-primary-500/30': toast.type === 'info'
                    }"
                >
                    <svg x-show="toast.type === 'success'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="toast.type.charAt(0).toUpperCase() + toast.type.slice(1) + '!'"></p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="toast.message"></p>
                </div>
                <button @click="remove(toast.id)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes shrink {
        from { width: 100%; }
        to { width: 0%; }
    }
</style>
