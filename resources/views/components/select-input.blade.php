@props([
    'name',
    'label' => null,
    'value' => '',
    'options' => [],
    'placeholder' => 'Select...',
    'required' => false,
    'size' => 'default', // default, sm
])

@php
$sizeClasses = $size === 'sm' 
    ? 'pl-3 pr-8 py-2 text-sm' 
    : 'pl-4 pr-10 py-2.5';
@endphp

<div class="w-full min-w-[140px]">
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {{ $label }}
        @if($required)
        <span class="text-red-500 ml-0.5">*</span>
        @endif
    </label>
    @endif

    <div class="relative">
        <select 
            name="{{ $name }}" 
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => "w-full {$sizeClasses} bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none cursor-pointer transition-colors"
            ]) }}
        >
            @if($placeholder)
            <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ (string)$value === (string)$optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    @error($name)
    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
