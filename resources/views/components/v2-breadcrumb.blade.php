@props(['items' => []])

<nav class="flex items-center gap-2 text-sm mb-6" aria-label="Breadcrumb">
    <a href="{{ auth()->user()->is_super_admin ? route('admin.dashboard') : route('v2.dashboard', ['company' => app()->bound('current.company') ? app('current.company')->slug : 'system-administration']) }}" 
       class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </a>
    
    @foreach($items as $item)
    <svg class="w-4 h-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    
    @if(isset($item['url']) && !$loop->last)
    <a href="{{ $item['url'] }}" class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
        {{ $item['label'] }}
    </a>
    @else
    <span class="text-gray-900 dark:text-white font-medium">
        {{ $item['label'] }}
    </span>
    @endif
    @endforeach
</nav>
