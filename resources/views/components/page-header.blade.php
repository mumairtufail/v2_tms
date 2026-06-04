@props(['title', 'description' => null, 'breadcrumbs' => null])

<div {{ $attributes->merge(['class' => '']) }}>
    {{-- Breadcrumbs --}}
    @if($breadcrumbs)
    <x-v2-breadcrumb :items="$breadcrumbs" />
    @endif
    
    {{-- Header with optional actions --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
            @if($description)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>
        
        {{-- Actions Slot --}}
        @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
        @endif
    </div>
</div>
