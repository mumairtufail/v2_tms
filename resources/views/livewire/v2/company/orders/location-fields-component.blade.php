<div class="grid grid-cols-1 gap-4 p-4 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 shadow-sm"
     x-data="{
        prefix: @js($prefix),
        dropdownOpen: @entangle('showDropdown'),
        localStop: @entangle('stop')
     }">
    
    {{-- Row 1: Company Name with Autocomplete --}}
    <div class="relative">
        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Company Name</label>
        
        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.300ms="query"
                   @focus="dropdownOpen = true"
                   class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2.5 shadow-sm transition-all"
                   placeholder="Search contacts or type address...">

            <div wire:loading wire:target="query, searchGoogle" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        {{-- Dropdown --}}
        <div x-show="dropdownOpen && $wire.query.length >= 2"
             class="absolute left-0 right-0 top-full mt-1.5 z-[100] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm"
             @mousedown.outside="dropdownOpen = false"
             x-cloak>

            {{-- Results --}}
            <div>
                <div class="px-3 py-2 bg-indigo-50/50 dark:bg-indigo-900/20 border-b border-indigo-100/50 dark:border-indigo-800 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg">
                                <path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4"/>
                                <path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853"/>
                                <path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04"/>
                                <path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335"/>
                            </svg>
                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Google Locations</span>
                        </div>
                </div>

                <div class="max-h-64 overflow-y-auto">
                        @if(strlen($query) >= 3)
                            @forelse($googleResults as $result)
                                <button type="button"
                                        @mousedown.prevent="
                                            console.log('Selecting place:', '{{ $result['placeId'] }}');
                                            dropdownOpen = false;
                                            $wire.selectGoogle('{{ $result['placeId'] }}');
                                        "
                                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex flex-col gap-1 border-b border-slate-50 dark:border-slate-800 last:border-0 transition-colors group">
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                        {{ $result['mainText'] }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $result['secondaryText'] }}
                                    </span>
                                </button>
                            @empty
                                <div class="px-4 py-8 text-center" wire:loading.remove wire:target="query">
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">No global locations found</p>
                                </div>
                            @endforelse
                        @else
                            <div class="px-4 py-4 text-center">
                                <p class="text-[10px] text-slate-400 italic">Type at least 3 characters to search...</p>
                            </div>
                        @endif
                        <div wire:loading wire:target="query" class="px-4 py-8 flex flex-col items-center gap-3">
                            <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs text-slate-500">Querying Google Maps...</span>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    {{-- Address 1 --}}
    <div class="relative">
        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Address Line 1</label>
        <div class="relative" wire:loading.class="opacity-50 animate-pulse" wire:target="selectGoogle">
            <input type="text"
                   wire:model="stop.{{ $prefix }}.address_1"
                   class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
                   placeholder="123 Street Ave">
            
            <div wire:loading wire:target="selectGoogle" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Address 2 --}}
    <div wire:loading.class="opacity-50 animate-pulse" wire:target="selectGoogle">
        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Address Line 2 (Optional)</label>
        <input type="text"
               wire:model="stop.{{ $prefix }}.address_2"
               class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
               placeholder="Suite, unit, floor, etc.">
    </div>

    {{-- City / State / Zip --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3" wire:loading.class="opacity-50 animate-pulse" wire:target="selectGoogle">
        <div class="md:col-span-5">
            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">City</label>
            <input type="text"
                   wire:model="stop.{{ $prefix }}.city"
                   class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
                   placeholder="City">
        </div>
        <div class="md:col-span-3">
            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">State</label>
            <input type="text"
                   wire:model="stop.{{ $prefix }}.state"
                   class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
                   placeholder="ST">
        </div>
        <div class="md:col-span-4">
            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Zip Code</label>
            <input type="text"
                   wire:model="stop.{{ $prefix }}.zip"
                   class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
                   placeholder="12345">
        </div>
    </div>

    {{-- Country --}}
    <div wire:loading.class="opacity-50 animate-pulse" wire:target="selectGoogle">
        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Country</label>
        <input type="text"
               wire:model="stop.{{ $prefix }}.country"
               class="block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
               placeholder="Country Code (e.g. US, DE)">
    </div>

    {{-- Contact Info Split --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-gray-100 dark:border-gray-800/50">
        <div>
            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Contact Name</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input type="text"
                       wire:model="stop.{{ $prefix }}.contact_name"
                       class="block w-full pl-9 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
                       placeholder="Full Name">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Phone Number</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </span>
                <input type="text"
                       wire:model="stop.{{ $prefix }}.phone"
                       class="block w-full pl-9 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 py-2 shadow-sm"
                       placeholder="(555) 000-0000">
            </div>
        </div>
    </div>
</div>