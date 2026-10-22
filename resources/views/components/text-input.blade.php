@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'required' => false,
    'value' => null,
    'type' => 'text',
    'placeholder' => null,
])

@if($label)
<div class="w-full">
    <x-input-label :for="$name" :value="$label" :required="$required" />
@endif

<input 
    type="{{ $type }}"
    {{-- An explicit id wins. It used to be written after id="{{ $name }}", so the
         browser kept the first one and every field named "name" shared one id,
         which broke <label for> and any script looking a field up by its id. --}}
    @if($name) name="{{ $name }}" @endif
    @if($attributes->has('id') || $name) id="{{ $attributes->get('id', $name) }}" @endif
    @if($value !== null) value="{{ $value }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->except('id')->merge([
        'class' => 'w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed'
    ]) !!}
>

@if($name)
<x-input-error :messages="$errors->get($name)" class="mt-1" />
@endif

@if($label)
</div>
@endif
