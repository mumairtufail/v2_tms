@props([
    'position' => 'bottom-right',
    'maxToasts' => 5,
    'progressBarVariant' => 'default',
    'progressBarAlignment' => 'bottom'
])

@php
    $positionClasses = match($position) {
        'top-left' => 'top-4 left-4',
        'top-right' => 'top-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        'bottom-right' => 'bottom-4 right-4',
        default => 'bottom-4 right-4'
    };
    
    $sessionToast = session()->pull('notify');
@endphp

<!-- Toast Container -->
<div
    x-data="toastComponent({
        maxToasts: {{ $maxToasts }},
        sessionToast: {{ json_encode($sessionToast) }}
    })"
    class="fixed {{ $positionClasses }} z-[9999] flex flex-col gap-3 pointer-events-none"
    style="max-width: 420px; width: 100%;"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @mouseenter="toast.isPaused = true"
            @mouseleave="toast.isPaused = false"
            :class="getColors(toast.type).bg + ' ' + getColors(toast.type).border"
            class="relative rounded-lg border shadow-lg backdrop-blur-sm pointer-events-auto overflow-hidden"
        >
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Bar -->
            <div 
                x-show="toast.progress > 0"
                :class="[
                    getColors(toast.type).progress,
                    '{{ $progressBarAlignment }}' === 'top' ? 'top-0' : 'bottom-0',
                    '{{ $progressBarVariant }}' === 'thin' ? 'h-1' : 'h-1.5'
                ]"
                class="absolute left-0 right-0 transition-all duration-75 ease-linear"
                :style="`width: ${toast.progress}%`"
            ></div>
        </div>
    </template>
</div>
