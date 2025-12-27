@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'required' => false,
    'placeholder' => null,
])

<div class="w-full" x-data="{ show: false }">
    @if($label)
    <x-input-label :for="$name" :value="$label" :required="$required" />
    @endif

    <div class="relative">
        <input 
            :type="show ? 'text' : 'password'"
            @if($name) name="{{ $name }}" id="{{ $name }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {!! $attributes->merge([
                'class' => 'w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed pr-10'
            ]) !!}
        >
        <button 
            type="button" 
            @click="show = !show" 
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
        >
            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>
    </div>

    @if($name)
    <x-input-error :messages="$errors->get($name)" class="mt-1" />
    @endif
</div>
