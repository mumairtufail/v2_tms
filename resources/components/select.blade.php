@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => 'Select an option',
    'options' => [],
    'value' => '',
    'required' => false,
    'disabled' => false,
    'icon' => null,
    'hint' => null,
    'error' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
    $selectedValue = old($name, $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                {!! $icon !!}
            </div>
        @endif

        <select 
            id="{{ $inputId }}"
            name="{{ $name }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->except('class')->merge([
                'class' => 'input pr-10 appearance-none cursor-pointer transition-all duration-200 ' . 
                    ($icon ? 'pl-12 ' : '') . 
                    ($hasError ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : '') .
                    ($disabled ? 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-800' : '')
            ]) }}
        >
            <option value="" disabled {{ !$selectedValue ? 'selected' : '' }}>{{ $placeholder }}</option>
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ $selectedValue == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>

        <!-- Dropdown Arrow -->
        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <x-input-error :messages="$error ? [$error] : $errors->get($name)" class="mt-2" />
    @endif
</div>
