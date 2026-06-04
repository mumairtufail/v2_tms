{{-- Searchable Select Component --}}
{{-- A dropdown with search functionality using Alpine.js --}}

@props([
    'name',
    'label' => null,
    'value' => '',
    'options' => [],
    'placeholder' => 'Select...',
    'searchPlaceholder' => 'Search...',
    'required' => false,
    'disabled' => false,
])

@php
    $selectedLabel = '';
    if ($value && isset($options[$value])) {
        $selectedLabel = $options[$value];
    } elseif ($value) {
        // Handle Collection objects
        foreach ($options as $key => $opt) {
            if ((string)$key === (string)$value) {
                $selectedLabel = $opt;
                break;
            }
        }
    }
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        selected: '{{ $value }}',
        selectedLabel: '{{ $selectedLabel }}',
        options: {{ json_encode(collect($options)->map(fn($label, $value) => ['value' => (string)$value, 'label' => $label])->values()) }},
        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(opt => 
                opt.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        selectOption(value, label) {
            this.selected = value;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
        },
        clear() {
            this.selected = '';
            this.selectedLabel = '';
        }
    }"
    class="w-full"
    @click.outside="open = false"
>
    @if($label)
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        {{ $label }}
        @if($required)<span class="text-red-500 ml-0.5">*</span>@endif
    </label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="selected" {{ $required ? 'required' : '' }}>

    <div class="relative">
        {{-- Trigger Button --}}
        <button
            type="button"
            @click="open = !open"
            :disabled="{{ $disabled ? 'true' : 'false' }}"
            class="w-full flex items-center justify-between gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-left transition-colors focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="{ 'border-primary-500 ring-1 ring-primary-500': open }"
        >
            <span x-text="selectedLabel || '{{ $placeholder }}'" :class="{ 'text-gray-500': !selectedLabel }" class="truncate text-gray-900 dark:text-white"></span>
            <div class="flex items-center gap-1">
                <button 
                    x-show="selected" 
                    x-cloak
                    type="button"
                    @click.stop="clear()" 
                    class="p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        {{-- Dropdown --}}
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-cloak
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden"
        >
            {{-- Search Input --}}
            <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input 
                        type="text"
                        x-model="search"
                        placeholder="{{ $searchPlaceholder }}"
                        @click.stop
                        class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                    >
                </div>
            </div>

            {{-- Options --}}
            <div class="max-h-48 overflow-y-auto">
                <template x-for="option in filteredOptions" :key="option.value">
                    <button
                        type="button"
                        @click="selectOption(option.value, option.label)"
                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center justify-between"
                        :class="{ 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300': selected === option.value }"
                    >
                        <span x-text="option.label" class="truncate"></span>
                        <svg x-show="selected === option.value" class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>
                <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                    No results found
                </div>
            </div>
        </div>
    </div>

    @error($name)
    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
