{{-- Search Highlight Component --}}
{{-- Highlights matching search terms within text --}}

@props(['text', 'search' => null])

@if($search && strlen($search) > 0)
    @php
        $pattern = preg_quote($search, '/');
        $highlighted = preg_replace(
            "/($pattern)/i", 
            '<mark class="bg-yellow-200 dark:bg-yellow-500/30 text-gray-900 dark:text-yellow-200 px-0.5 rounded">$1</mark>', 
            e($text)
        );
    @endphp
    {!! $highlighted !!}
@else
    {{ $text }}
@endif
