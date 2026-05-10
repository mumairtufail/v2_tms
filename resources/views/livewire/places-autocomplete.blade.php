<div
    x-data="companyAutocomplete()"
    @mousedown.outside="close()"
    @place-selected="
        window.dispatchEvent(new CustomEvent('places-fill-{{ $prefix }}', { detail: $event.detail }));
    ">

    <label class="block text-[10px] font-medium text-gray-400 uppercase">Company Name</label>

    <div class="relative group">
        <input type="text"
               x-model="query"
               @input.debounce.300ms="search"
               @focus="if(query.length >= 2) showDropdown = true"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Search company or address...">

        <div x-show="isLoading || isGoogleLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg class="animate-spin h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    {{-- Dropdown --}}
    <div x-show="showDropdown"
         class="absolute left-0 right-0 top-full mt-1 z-[100] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl overflow-hidden"
         x-cloak>

        {{-- Local search loading --}}
        <template x-if="isLoading">
            <div class="flex items-center gap-2.5 px-4 py-3.5">
                <svg class="animate-spin h-4 w-4 text-primary-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-[12px] text-slate-500 dark:text-slate-400">Searching contacts…</span>
            </div>
        </template>

        {{-- Local results (mode=local, not loading) --}}
        <template x-if="!isLoading && mode === 'local'">
            <div>
                {{-- Local contact results --}}
                <template x-if="results.length > 0">
                    <div>
                        <div class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Contacts</span>
                        </div>
                        <div class="max-h-44 overflow-y-auto">
                            <template x-for="customer in results" :key="customer.id">
                                <button type="button"
                                        @mousedown.prevent="selectLocal(customer)"
                                        class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex flex-col gap-0.5 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="customer.name"></span>
                                    <span class="text-[10px] text-slate-400" x-text="[customer.city, customer.state].filter(Boolean).join(', ')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- No local results — Search with Google CTA --}}
                <template x-if="results.length === 0 && query.length >= 2">
                    <div class="px-4 py-3 text-center">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">No contacts found for "<span class="font-medium text-slate-700 dark:text-slate-200" x-text="query"></span>"</p>
                    </div>
                </template>

                {{-- Search with Google button (always shown when query >= 3) --}}
                <template x-if="query.length >= 3">
                    <button type="button"
                            @mousedown.prevent="searchGoogle()"
                            class="w-full flex items-center gap-2.5 px-4 py-3 border-t border-slate-100 dark:border-slate-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group">
                        <span class="flex-shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg">
                                <path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4"/>
                                <path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853"/>
                                <path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04"/>
                                <path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335"/>
                            </svg>
                        </span>
                        <span class="text-[12px] font-semibold text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                            Search "<span x-text="query"></span>" with Google
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>
            </div>
        </template>

        {{-- Google search loading --}}
        <template x-if="mode === 'google' && isGoogleLoading">
            <div class="flex items-center gap-2.5 px-4 py-3.5">
                <svg class="animate-spin h-4 w-4 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-[12px] text-slate-500 dark:text-slate-400">Searching Google Places…</span>
            </div>
        </template>

        {{-- Google results --}}
        <template x-if="mode === 'google' && !isGoogleLoading">
            <div>
                <div class="px-3 py-1.5 flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg">
                        <path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4"/>
                        <path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853"/>
                        <path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04"/>
                        <path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335"/>
                    </svg>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Google Places</span>
                </div>

                <template x-if="googleResults.length > 0">
                    <div class="max-h-52 overflow-y-auto">
                        <template x-for="result in googleResults" :key="result.placeId">
                            <button type="button"
                                    @mousedown.prevent="select(result)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex flex-col gap-0.5 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="result.mainText"></span>
                                <span class="text-[10px] text-slate-400" x-text="result.secondaryText"></span>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="googleResults.length === 0">
                    <div class="px-4 py-4 text-center">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No results found for "<span class="font-medium text-slate-700 dark:text-slate-200" x-text="query"></span>"</p>
                        <p class="text-[10px] text-slate-400 mt-1">Try a different name or address</p>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
