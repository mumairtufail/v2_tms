@props([
    'items' => [],
    'separator' => null,
])

{{-- 
    Usage:
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Create User'], // Last item without URL = current page
    ]" />
--}}

<nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
    <!-- Home Icon -->
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </a>

    @foreach($items as $index => $item)
        <!-- Separator -->
        <span class="text-gray-300 dark:text-gray-600">
            @if($separator)
                {{ $separator }}
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif
        </span>

        @if(isset($item['url']) && $index !== count($items) - 1)
            <!-- Link Item -->
            <a 
                href="{{ $item['url'] }}" 
                class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200 font-medium"
            >
                {{ $item['label'] }}
            </a>
        @else
            <!-- Current Page (Last Item) -->
            <span class="text-gray-900 dark:text-white font-semibold">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
