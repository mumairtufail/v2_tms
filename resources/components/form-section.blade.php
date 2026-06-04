@props(['title', 'description'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm']) }}>
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        @if(!empty($description))
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        @endif
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
