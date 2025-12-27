@props(['label', 'name', 'options' => [], 'value' => '', 'placeholder' => 'Select...', 'required' => false, 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {{ $label }}
        @if($required)
        <span class="text-red-500">*</span>
        @endif
    </label>
    <select 
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-shadow @error($name) border-red-500 @enderror"
        {{ $attributes }}
    >
        @if($placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $optValue => $optLabel)
        <option value="{{ $optValue }}" {{ old($name, $value) == $optValue ? 'selected' : '' }}>
            {{ $optLabel }}
        </option>
        @endforeach
    </select>
    @if($hint)
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
