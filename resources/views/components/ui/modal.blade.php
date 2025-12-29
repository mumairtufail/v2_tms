@props([
    'id',
    'heading' => null,
    'description' => null,
    'persistent' => false,
    'stickyHeader' => false,
    'stickyFooter' => false,
    'alignment' => 'left',
    'closeButton' => true,
    'maxWidth' => '2xl', // sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl, full
])

@php
    $maxWidthClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        '6xl' => 'sm:max-w-6xl',
        '7xl' => 'sm:max-w-7xl',
        'full' => 'sm:max-w-full',
    ][$maxWidth];

    $alignmentClasses = match($alignment) {
        'center' => 'text-center',
        'right', 'end' => 'text-right',
        default => 'text-left',
    };
@endphp

<div
    x-data="{ 
        show: false,
        id: '{{ $id }}',
        persistent: {{ $persistent ? 'true' : 'false' }},
        close() { 
            this.show = false;
            // Dispatch event for parent components
            this.$dispatch('modal-closed', { id: this.id });
        },
        open() { 
            this.show = true; 
            // Dispatch event for parent components
            this.$dispatch('modal-opened', { id: this.id });
        }
    }"
    x-on:open-modal.window="if ($event.detail === id) open()"
    x-on:close-modal.window="if ($event.detail === id) close()"
    x-on:keydown.escape.window="if (!persistent && show) close()"
    x-show="show"
    class="relative z-[9999]"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
        @click="if (!persistent) close()"
    ></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 {{ $alignmentClasses }} shadow-xl transition-all sm:my-8 w-full {{ $maxWidthClasses }} flex flex-col max-h-[90vh]"
            >
                <!-- Header -->
                @if($heading || $closeButton || $stickyHeader)
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 {{ $stickyHeader ? 'sticky top-0 z-10' : '' }}">
                        <div class="flex-1">
                            @if($heading)
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                    {{ $heading }}
                                </h3>
                            @endif
                            @if($description)
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $description }}
                                </p>
                            @endif
                        </div>
                        
                        @if($closeButton)
                            <button @click="close()" type="button" class="ml-4 rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Content -->
                <div class="flex-1 overflow-y-auto px-4 py-4">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if(isset($footer))
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 border-t border-gray-200 dark:border-gray-700 {{ $stickyFooter ? 'sticky bottom-0 z-10' : '' }}">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
