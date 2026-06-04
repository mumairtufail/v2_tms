@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-4 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-xl']) }}>
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-success-600 dark:text-success-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-success-800 dark:text-success-200">
                {{ $status }}
            </p>
        </div>
    </div>
@endif
