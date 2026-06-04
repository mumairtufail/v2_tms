{{--
    24-Hour Time Picker  –  <x-time-picker x-model="someVar" />
    Value in/out: "HH:mm"  |  No AM/PM, no external libraries.
--}}
<div
    x-data="{
        value: '',
        open:  false,
        selH:  null,
        selM:  null,

        init() {
            this._parse(this.value);
            this.$watch('value', v => this._parse(v));
            this.$watch('open', isOpen => {
                if (!isOpen) return;
                this.$nextTick(() => this._scrollToSelected());
            });
        },

        _parse(v) {
            if (!v) { this.selH = null; this.selM = null; return; }
            const p = String(v).split(':');
            const h = parseInt(p[0]), m = parseInt(p[1] ?? 0);
            if (!isNaN(h)) this.selH = h;
            if (!isNaN(m)) this.selM = m;
        },

        _scrollToSelected() {
            const itemH = 36;
            const hEl = this.$el.querySelector('.tp-hour-scroll');
            const mEl = this.$el.querySelector('.tp-min-scroll');
            if (hEl && this.selH !== null)
                hEl.scrollTop = Math.max(0, this.selH * itemH - hEl.clientHeight / 2 + itemH / 2);
            if (mEl && this.selM !== null) {
                const mIdx = Math.round(this.selM / 5);
                mEl.scrollTop = Math.max(0, mIdx * itemH - mEl.clientHeight / 2 + itemH / 2);
            }
        },

        pickH(h) {
            this.selH = h;
            if (this.selM === null) this.selM = 0;
            this._save();
        },

        pickM(m) {
            this.selM = m;
            if (this.selH === null) this.selH = 0;
            this._save();
            this.open = false;
        },

        _save() {
            this.value = String(this.selH).padStart(2,'0') + ':' + String(this.selM).padStart(2,'0');
            this.$dispatch('change');
        },

        clear() { this.value = ''; this.selH = null; this.selM = null; this.open = false; },

        get label() {
            return this.selH === null
                ? '-- : --'
                : String(this.selH).padStart(2,'0') + ' : ' + String(this.selM ?? 0).padStart(2,'0');
        }
    }"
    x-modelable="value"
    {{ $attributes->class(['relative inline-block w-full']) }}
>
    {{-- ── Trigger ──────────────────────────────────────────── --}}
    <button type="button" @click="open = !open"
        class="flex items-center justify-between w-full gap-2 px-3 py-1.5 text-sm
               bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
               rounded-lg hover:border-primary-400 focus:outline-none
               focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500
               transition-all cursor-pointer select-none">
        <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="label" class="font-mono text-sm font-semibold tracking-widest"
                  :class="selH === null ? 'text-gray-400' : 'text-gray-800 dark:text-gray-100'"></span>
        </div>
        <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- ── Dropdown ──────────────────────────────────────────── --}}
    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute z-50 top-full mt-1.5 left-0
                bg-white dark:bg-gray-800
                border border-gray-200 dark:border-gray-700
                rounded-xl shadow-xl overflow-hidden w-44">

        {{-- Column headers --}}
        <div class="flex border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex-1 py-1.5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hour</div>
            <div class="w-px bg-gray-100 dark:bg-gray-700"></div>
            <div class="flex-1 py-1.5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Min</div>
        </div>

        {{-- Scrollable columns --}}
        <div class="flex">
            {{-- Hours 00–23 --}}
            <div class="tp-hour-scroll flex-1 h-52 overflow-y-auto overscroll-contain py-1">
                @for ($h = 0; $h < 24; $h++)
                <button type="button" @click="pickH({{ $h }})"
                        :class="selH === {{ $h }}
                            ? 'bg-primary-500 text-white font-bold mx-1 rounded-lg'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60'"
                        class="w-[calc(100%-8px)] mx-1 h-9 text-sm font-mono text-center transition-colors rounded-lg">
                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                </button>
                @endfor
            </div>

            <div class="w-px bg-gray-100 dark:bg-gray-700 self-stretch"></div>

            {{-- Minutes 00–55 (every 5 min) --}}
            <div class="tp-min-scroll flex-1 h-52 overflow-y-auto overscroll-contain py-1">
                @for ($m = 0; $m < 60; $m += 5)
                <button type="button" @click="pickM({{ $m }})"
                        :class="selM === {{ $m }}
                            ? 'bg-primary-500 text-white font-bold mx-1 rounded-lg'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60'"
                        class="w-[calc(100%-8px)] mx-1 h-9 text-sm font-mono text-center transition-colors rounded-lg">
                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                </button>
                @endfor
            </div>
        </div>

        {{-- Footer --}}
        <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <button type="button" @click="clear()"
                    class="w-full py-1.5 text-[10px] text-gray-400
                           hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20
                           transition-colors text-center">
                Clear
            </button>
        </div>
    </div>
</div>
