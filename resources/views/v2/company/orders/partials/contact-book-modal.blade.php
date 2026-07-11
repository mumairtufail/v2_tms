{{-- Save to Contact Book dialog — shown right before order submit when new addresses are detected --}}
<div x-show="showContactBookModal"
     x-cloak
     class="fixed inset-0 z-[70] flex items-center justify-center p-4"
     @keydown.escape.window="if (showContactBookModal) skipContactBookSave()">

    {{-- Backdrop --}}
    <div x-show="showContactBookModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"
         @click="skipContactBookSave()"></div>

    {{-- Panel --}}
    <div x-show="showContactBookModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-start gap-3 px-6 pt-6 pb-4">
            <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Save to Contact Book</h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">These addresses aren't in your contact book yet. Save them for faster entry next time?</p>
            </div>
        </div>

        {{-- Candidate list --}}
        <div class="px-6 pb-2 max-h-72 overflow-y-auto space-y-2">
            <template x-for="(c, i) in contactBookCandidates" :key="i">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition-colors">
                    <input type="checkbox"
                           x-model="c.checked"
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:bg-gray-800">
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="c.name"></span>
                            <span class="flex-shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide"
                                  :class="c.role === 'shipper'
                                      ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'
                                      : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'"
                                  x-text="c.role"></span>
                            <span class="flex-shrink-0 text-[10px] text-slate-400" x-text="'Stop ' + (c.stopIndex + 1)"></span>
                        </span>
                        <span class="block mt-0.5 text-xs text-slate-500 dark:text-slate-400 truncate"
                              x-text="[c.address_1, c.city, c.state].filter(Boolean).join(', ')"></span>
                    </span>
                </label>
            </template>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 mt-2 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-100 dark:border-slate-800">
            <button type="button"
                    @click="skipContactBookSave()"
                    class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                Skip
            </button>
            <button type="button"
                    @click="confirmContactBookSave()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save & continue
            </button>
        </div>
    </div>
</div>
