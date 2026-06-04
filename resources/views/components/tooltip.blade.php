{{-- Tooltip Component --}}
{{-- Wraps content with a tooltip that appears on hover --}}

@props([
    'text',
    'position' => 'top', // top, bottom, left, right
])

@php
$positionClasses = [
    'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
    'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
];
@endphp

<div 
    x-data="{ show: false }" 
    class="relative inline-flex"
    @mouseenter="show = true"
    @mouseleave="show = false"
>
    {{-- Trigger Content --}}
    {{ $slot }}
    
    {{-- Tooltip --}}
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="absolute z-[100] {{ $positionClasses[$position] ?? $positionClasses['top'] }} pointer-events-none"
    >
        <div class="px-2.5 py-1.5 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg whitespace-nowrap">
            {{ $text }}
        </div>
    </div>
</div>
