{{-- Compact Filter Select Component --}}
{{-- Used in index page filter bars for inline filtering --}}

@props([
    'name',
    'value' => '',
    'options' => [],
    'placeholder' => 'All',
])

<select 
    name="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'px-4 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 min-w-[120px] cursor-pointer'
    ]) }}
>
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $optionValue => $optionLabel)
    <option value="{{ $optionValue }}" {{ (string)$value === (string)$optionValue ? 'selected' : '' }}>
        {{ $optionLabel }}
    </option>
    @endforeach
</select>
