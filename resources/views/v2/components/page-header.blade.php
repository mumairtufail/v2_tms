@props(['title', 'description' => null, 'breadcrumbs' => []])

<div class="mb-6">
    <!-- Breadcrumbs -->
    @if(count($breadcrumbs) > 0)
    <nav class="flex mb-2" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 text-sm text-gray-500 dark:text-gray-400">
            <li class="inline-flex items-center">
                <a href="{{ route('v2.dashboard', ['company' => app('current.company')]) }}" 
                   class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </a>
            </li>
            @foreach($breadcrumbs as $crumb)
            <li>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    @if(isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="ml-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        {{ $crumb['label'] }}
                    </a>
                    @else
                    <span class="ml-1 text-gray-900 dark:text-white font-medium">{{ $crumb['label'] }}</span>
                    @endif
                </div>
            </li>
            @endforeach
        </ol>
    </nav>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
            @if($description)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
        @endisset
    </div>
</div>
