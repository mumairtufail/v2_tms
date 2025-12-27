@props(['title', 'description' => null, 'breadcrumbs' => []])

<div class="mb-6">
    @if(!empty($breadcrumbs))
        <div class="mb-4">
            <x-breadcrumbs :items="$breadcrumbs" />
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $title }}</h1>
            @if($description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $description }}</p>
            @endif
        </div>
        
        @if(isset($actions))
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
