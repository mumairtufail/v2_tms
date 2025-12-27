{{-- Global Toast Notifications Component --}}
{{-- This component renders flash messages (success, error, warning, info) as toast notifications --}}
{{-- Uses Alpine.js for animations and auto-dismiss functionality --}}

<div 
    x-data="{ 
        toasts: [],
        addToast(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message, show: false });
            setTimeout(() => {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) toast.show = true;
            }, 50);
            setTimeout(() => this.removeToast(id), 5000);
        },
        removeToast(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) {
                toast.show = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    }"
    x-init="
        @if(session('success'))
            addToast('success', @json(session('success')));
        @endif
        @if(session('error'))
            addToast('error', @json(session('error')));
        @endif
        @if(session('warning'))
            addToast('warning', @json(session('warning')));
        @endif
        @if(session('info'))
            addToast('info', @json(session('info')));
        @endif
    "
    class="fixed bottom-4 right-4 z-[9999] space-y-3 pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="pointer-events-auto flex items-start gap-3 min-w-[320px] max-w-md p-4 rounded-xl shadow-lg border backdrop-blur-sm"
            :class="{
                'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800': toast.type === 'success',
                'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800': toast.type === 'error',
                'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800': toast.type === 'warning',
                'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800': toast.type === 'info'
            }"
        >
            {{-- Icon --}}
            <div class="flex-shrink-0">
                {{-- Success Icon --}}
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                {{-- Error Icon --}}
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                {{-- Warning Icon --}}
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </template>
                {{-- Info Icon --}}
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </div>
            
            {{-- Message --}}
            <p 
                class="flex-1 text-sm font-medium"
                :class="{
                    'text-green-800 dark:text-green-200': toast.type === 'success',
                    'text-red-800 dark:text-red-200': toast.type === 'error',
                    'text-yellow-800 dark:text-yellow-200': toast.type === 'warning',
                    'text-blue-800 dark:text-blue-200': toast.type === 'info'
                }"
                x-text="toast.message"
            ></p>
            
            {{-- Close Button --}}
            <button 
                @click="removeToast(toast.id)" 
                class="flex-shrink-0 p-1 rounded-lg transition-colors"
                :class="{
                    'text-green-500 hover:bg-green-100 dark:hover:bg-green-800/50': toast.type === 'success',
                    'text-red-500 hover:bg-red-100 dark:hover:bg-red-800/50': toast.type === 'error',
                    'text-yellow-500 hover:bg-yellow-100 dark:hover:bg-yellow-800/50': toast.type === 'warning',
                    'text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-800/50': toast.type === 'info'
                }"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
