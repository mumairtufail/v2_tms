@props([
    'name' => '',
    'label' => null,
    'options' => [],
    'value' => '',
    'required' => false,
    'disabled' => false,
    'inline' => false,
    'error' => null,
])

@php
    $selectedValue = old($name, $value);
    $hasError = $error || $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <label class="form-label mb-3">
            {{ $label }}
            @if($required)
                <span class="text-danger-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    <div class="{{ $inline ? 'flex flex-wrap gap-6' : 'space-y-3' }}">
        @foreach($options as $optionValue => $optionLabel)
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input 
                        type="radio"
                        id="{{ $name }}_{{ $optionValue }}"
                        name="{{ $name }}"
                        value="{{ $optionValue }}"
                        @if($selectedValue == $optionValue) checked @endif
                        @if($required) required @endif
                        @if($disabled) disabled @endif
                        class="h-5 w-5 border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:ring-offset-0 transition-all duration-200 cursor-pointer
                               bg-white dark:bg-gray-800
                               checked:border-primary-500
                               hover:border-primary-400 dark:hover:border-primary-500
                               {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }}
                               {{ $hasError ? 'border-danger-500' : '' }}"
                    >
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="{{ $name }}_{{ $optionValue }}" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                        {{ $optionLabel }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    @if($hasError)
        <x-input-error :messages="$error ? [$error] : $errors->get($name)" class="mt-2" />
    @endif
</div>
