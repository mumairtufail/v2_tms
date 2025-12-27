@props(['noPadding' => false])

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
    @isset($pagination)
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
        {{ $pagination }}
    </div>
    @endisset
</div>
