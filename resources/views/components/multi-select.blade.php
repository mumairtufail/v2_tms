{{-- Searchable Multi-Select Component --}}
{{-- A dropdown for selecting multiple options with search functionality --}}

@props([
    'name',
    'label' => null,
    'value' => [],
    'options' => [],
    'placeholder' => 'Select options...',
    'searchPlaceholder' => 'Search...',
    'required' => false,
])

@php
    $selectedValues = is_array($value) ? $value : (is_object($value) ? $value->toArray() : []);
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        selected: {{ json_encode(array_map('strval', $selectedValues)) }},
        options: {{ json_encode(collect($options)->map(fn($label, $value) => ['value' => (string)$value, 'label' => $label])->values()) }},
        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(opt => 
                opt.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        get selectedLabels() {
            return this.options.filter(opt => this.selected.includes(opt.value)).map(opt => opt.label);
        },
        isSelected(value) {
            return this.selected.includes(value);
        },
        toggleOption(value) {
            if (this.isSelected(value)) {
                this.selected = this.selected.filter(v => v !== value);
            } else {
                this.selected.push(value);
            }
        },
        removeOption(value) {
            this.selected = this.selected.filter(v => v !== value);
        },
        clearAll() {
            this.selected = [];
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

    {{-- Hidden inputs for form submission --}}
    <template x-for="val in selected" :key="val">
        <input type="hidden" :name="'{{ $name }}[]'" :value="val">
    </template>

    <div class="relative">
        {{-- Trigger Button --}}
        <button
            type="button"
            @click="open = !open"
            class="w-full min-h-[42px] flex items-center justify-between gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-left transition-colors focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
            :class="{ 'border-primary-500 ring-1 ring-primary-500': open }"
        >
            <div class="flex flex-wrap gap-1 flex-1">
                <template x-if="selected.length === 0">
                    <span class="text-gray-500 py-0.5">{{ $placeholder }}</span>
                </template>
                <template x-for="val in selected" :key="val">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-300 text-sm rounded-md">
                        <span x-text="options.find(o => o.value === val)?.label"></span>
                        <button type="button" @click.stop="removeOption(val)" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button 
                    x-show="selected.length > 0" 
                    x-cloak
                    type="button"
                    @click.stop="clearAll()" 
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
                        @click="toggleOption(option.value)"
                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3"
                        :class="{ 'bg-primary-50 dark:bg-primary-900/20': isSelected(option.value) }"
                    >
                        <div 
                            class="w-4 h-4 border rounded flex items-center justify-center transition-colors"
                            :class="isSelected(option.value) ? 'bg-primary-600 border-primary-600' : 'border-gray-300 dark:border-gray-600'"
                        >
                            <svg x-show="isSelected(option.value)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span x-text="option.label" class="truncate text-gray-900 dark:text-white"></span>
                    </button>
                </template>
                <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                    No results found
                </div>
            </div>

            {{-- Footer --}}
            <div x-show="selected.length > 0" class="p-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    <span x-text="selected.length"></span> selected
                </p>
            </div>
        </div>
    </div>

    @error($name)
    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
