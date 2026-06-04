@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
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

    <textarea 
        id="{{ $inputId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->except('class')->merge([
            'class' => 'input resize-none transition-all duration-200 ' . 
                ($hasError ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : '') .
                ($disabled ? 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-800' : '')
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <x-input-error :messages="$error ? [$error] : $errors->get($name)" class="mt-2" />
    @endif
</div>
