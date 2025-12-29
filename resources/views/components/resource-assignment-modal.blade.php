@props([
    'type' => 'driver',
    'title' => 'Assign Resource',
    'description' => 'Select and assign resources',
    'emptyMessage' => 'No items found'
])

@php
    $typeUpper = ucfirst($type);
    $typePlural = $type === 'equipment' ? 'equipment' : $type . 's';
    $modalId = 'modal-' . $type;
    
    // Handle variable naming inconsistencies in parent component
    if ($type === 'equipment') {
        $selectedVar = 'selectedEquipment';
        $loadingVar = 'loadingEquipment';
        $filteredVar = 'filteredEquipments'; // edit.blade.php has this explicit getter
    } else {
        $selectedVar = 'selected' . $typeUpper . 's';
        $loadingVar = 'loading' . $typeUpper . 's';
        $filteredVar = 'filtered' . $typeUpper . 's';
    }
    
    $searchVar = $type . 'Search';
    
    // Wire up the open/close events to Alpine
    $showVar = 'show' . $typeUpper . 'Modal';
@endphp

<!-- Wrapper to bridge existing Alpine state with x-ui.modal -->
<div x-effect="if ({{ $showVar }}) $dispatch('open-modal', '{{ $modalId }}'); else $dispatch('close-modal', '{{ $modalId }}')"
     @modal-closed.window="if ($event.detail.id === '{{ $modalId }}') close{{ $typeUpper }}Modal()">
     
    <x-ui.modal 
        id="{{ $modalId }}"
        heading="{{ $title }}"
        description="{{ $description }}"
        maxWidth="5xl"
        persistent
    >
        <!-- Search Bar (Sticky within content area) -->
        <div class="mb-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" 
                       x-model="{{ $searchVar }}" 
                       placeholder="Search {{ $typePlural }}..." 
                       class="w-full pl-9 pr-4 py-2 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <!-- Split Layout Container (Always Row) -->
        <div class="grid grid-cols-2 gap-0 h-[500px] border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            
            <!-- LEFT: Available Items (50% Width) -->
            <div class="flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center flex-shrink-0">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Available</span>
                    <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full text-gray-600 dark:text-gray-300 font-medium" x-text="{{ $filteredVar }}.filter(i => !{{ $selectedVar }}.has(i.id)).length">0</span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-if="{{ $loadingVar }}">
                        <div class="flex justify-center py-12">
                            <svg class="w-8 h-8 text-primary-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </template>
                    
                    <template x-if="!{{ $loadingVar }} && {{ $filteredVar }}.length === 0">
                         <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <span class="text-sm">No items found</span>
                        </div>
                    </template>

                    <template x-for="item in {{ $filteredVar }}" :key="item.id">
                        <div x-show="!{{ $selectedVar }}.has(item.id)"
                             @click="toggle{{ $typeUpper }}(item.id)"
                             class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm rounded-lg cursor-pointer group transition-all">
                             <!-- Add Button -->
                            <div class="w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center text-gray-400 group-hover:text-white group-hover:bg-primary-500 transition-colors flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <!-- Text -->
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" 
                                     x-text="item.name || item.unit_number || item.carrier_name"></div>
                                <div class="text-xs text-gray-500 truncate" 
                                     x-text="item.email || item.type || item.dot_id || ''"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- RIGHT: Selected Items (50% Width) -->
            <div class="flex flex-col bg-gray-50/50 dark:bg-gray-900/30">
                <div class="px-4 py-3 bg-green-50/50 dark:bg-green-900/10 border-b border-green-100 dark:border-green-800/30 flex justify-between items-center flex-shrink-0">
                    <span class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase tracking-wider">Selected</span>
                    <span class="text-xs bg-green-100 dark:bg-green-900/40 px-2 py-0.5 rounded-full text-green-800 dark:text-green-300 font-medium" x-text="{{ $selectedVar }}.size">0</span>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-if="{{ $selectedVar }}.size === 0">
                        <div class="flex flex-col items-center justify-center h-full text-gray-400">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-500">No items selected</span>
                            <span class="text-xs text-gray-400 mt-1">Select items from the left to assign</span>
                        </div>
                    </template>

                    <template x-for="item in {{ $filteredVar }}.filter(i => {{ $selectedVar }}.has(i.id))" :key="'sel-' + item.id">
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800/30 rounded-lg shadow-sm hover:border-red-300 group transition-all">
                             <div class="min-w-0 flex-1 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                                         x-text="item.name || item.unit_number || item.carrier_name"></div>
                                    <div class="text-xs text-green-600 dark:text-green-400 truncate">Ready to assign</div>
                                </div>
                             </div>
                             <button @click="toggle{{ $typeUpper }}(item.id)" class="text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition-colors" title="Remove">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                             </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Custom Footer -->
        <x-slot name="footer">
            <div class="flex justify-between items-center w-full">
                <div class="text-sm text-gray-500">
                    <span class="font-medium text-gray-900 dark:text-white" x-text="{{ $selectedVar }}.size"></span> 
                    <span x-text="{{ $selectedVar }}.size === 1 ? 'item' : 'items'"></span> selected
                </div>
                <div class="flex space-x-3">
                    <button @click="close{{ $typeUpper }}Modal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Cancel
                    </button>
                    <button @click="$dispatch('save-resource', { type: '{{ $type }}' })" 
                            :disabled="savingResource"
                            class="px-5 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                        <div x-show="savingResource" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                        <span x-text="savingResource ? 'Saving...' : 'Confirm Assignment'"></span>
                        <svg x-show="!savingResource" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                </div>
            </div>
        </x-slot>
    </x-ui.modal>
</div>
