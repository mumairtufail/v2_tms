@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1.5']) }}>
    {{ $value ?? $slot }}
    @if($required)
    <span class="text-red-500 ml-0.5">*</span>
    @endif
</label>
