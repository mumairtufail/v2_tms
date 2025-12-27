@props(['filters'])

@php
    $hasActiveFilters = false;
    foreach ($filters as $filter) {
        if (request()->has($filter) && request($filter) !== '') {
            $hasActiveFilters = true;
            break;
        }
    }
@endphp

@if($hasActiveFilters)
<div class="flex flex-wrap items-center gap-2 mb-4">
    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Filters:</span>
    
    @foreach($filters as $filter)
        @if(request()->has($filter) && request($filter) !== '')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-primary-800">
                {{ ucfirst($filter) }}: {{ request($filter) }}
                <a href="{{ request()->fullUrlWithQuery([$filter => null]) }}" class="ml-1 hover:text-primary-900 dark:hover:text-primary-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </span>
        @endif
    @endforeach

    <a href="{{ request()->url() }}" class="text-xs text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 underline">
        Clear All
    </a>
</div>
@endif
