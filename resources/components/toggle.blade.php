@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'checked' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $inputId = $id ?? $name;
    $isChecked = old($name, $checked);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'flex items-center justify-between']) }}>
    <div class="flex-1">
        @if($label)
            <label for="{{ $inputId }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                {{ $label }}
            </label>
        @endif
        @if($hint)
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
        @endif
    </div>

    <button 
        type="button"
        role="switch"
        x-data="{ enabled: {{ $isChecked ? 'true' : 'false' }} }"
        @click="enabled = !enabled"
        :aria-checked="enabled"
        :class="enabled ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600'"
        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
        @if($disabled) disabled @endif
    >
        <span 
            :class="enabled ? 'translate-x-5' : 'translate-x-0'"
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
        ></span>
        <input 
            type="hidden" 
            name="{{ $name }}" 
            :value="enabled ? '1' : '0'"
        >
    </button>
</div>
