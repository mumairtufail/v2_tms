@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'checked' => false,
    'disabled' => false,
    'error' => null,
])

@php
    $inputId = $id ?? $name;
    $isChecked = old($name, $checked);
    $hasError = $error || $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'relative flex items-start']) }}>
    <div class="flex h-6 items-center">
        <input 
            type="checkbox"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="1"
            @if($isChecked) checked @endif
            @if($disabled) disabled @endif
            class="h-5 w-5 rounded-md border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:ring-offset-0 transition-all duration-200 cursor-pointer
                   bg-white dark:bg-gray-800 
                   checked:bg-primary-500 checked:border-primary-500
                   hover:border-primary-400 dark:hover:border-primary-500
                   {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }}
                   {{ $hasError ? 'border-danger-500' : '' }}"
        >
    </div>
    @if($label)
        <div class="ml-3 text-sm leading-6">
            <label for="{{ $inputId }}" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                {{ $label }}
            </label>
            {{ $slot }}
        </div>
    @endif
</div>

@if($hasError)
    <x-input-error :messages="$error ? [$error] : $errors->get($name)" class="mt-2" />
@endif
