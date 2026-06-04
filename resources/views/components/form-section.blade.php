{{-- Form Section Component --}}
{{-- Groups form fields with a title and description --}}

@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6']) }}>
    @if($title || $description)
    <div class="mb-6">
        @if($title)
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        @endif
        @if($description)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif
    </div>
    @endif
    
    {{ $slot }}
</div>
