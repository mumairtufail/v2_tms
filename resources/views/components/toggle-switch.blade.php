{{-- Toggle Switch Component --}}
{{-- A modern, visually appealing toggle switch for boolean values --}}

@props([
    'name',
    'label' => null,
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'value' => '1',
])

<div class="flex items-start gap-3">
    <div class="relative inline-flex items-center">
        <input 
            type="checkbox" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="sr-only peer"
            {{ $attributes }}
        >
        <label 
            for="{{ $name }}"
            class="relative w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full cursor-pointer transition-colors
                   peer-focus:ring-2 peer-focus:ring-primary-500/50
                   peer-checked:bg-primary-600 dark:peer-checked:bg-primary-500
                   peer-disabled:opacity-50 peer-disabled:cursor-not-allowed
                   after:content-[''] after:absolute after:top-0.5 after:left-0.5 
                   after:bg-white after:rounded-full after:h-5 after:w-5 
                   after:shadow-md after:transition-transform after:duration-200
                   peer-checked:after:translate-x-5"
        ></label>
    </div>
    @if($label || $description)
    <div class="flex-1">
        @if($label)
        <label 
            for="{{ $name }}" 
            class="block text-sm font-medium text-gray-900 dark:text-white cursor-pointer"
        >
            {{ $label }}
        </label>
        @endif
        @if($description)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    @endif
</div>

@error($name)
<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
