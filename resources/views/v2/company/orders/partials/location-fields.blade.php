{{-- Location Fields Partial for Order Form --}}
{{-- $prefix: 'shipper' or 'consignee' --}}
@php
    $loc = "stop.$prefix";
    $labelClass = 'block text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase';
    $inputClass = 'mt-1 block w-full py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500';
    $dateErrorClass = 'date-error-msg mt-1.5 ml-11 flex items-center gap-1.5 text-[11px] text-red-600 dark:text-red-400 font-medium bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-2.5 py-1.5 rounded-md';
@endphp

<div class="p-4 space-y-4"
     x-data="{
        ...companyAutocomplete({
            initialQuery: {{ $loc }}.company_name,
            prefix: '{{ $prefix }}',
            stopIndex: stopIndex,
            contactBookUrl: '{{ route('v2.contact-book.index', array_filter(['company' => $company->slug, 'customer_id' => $order->customer_id ?? null])) }}'
        }),
        _stopIndex: stopIndex,
        showDetails: false,
        countryName(code) {
            if (!code) return '';
            try {
                return new Intl.DisplayNames(['en'], { type: 'region' }).of(String(code).toUpperCase()) || code;
            } catch (e) {
                return code;
            }
        }
     }"
     @google-place-selected.window="
        if ($event.detail.targetIndex === _stopIndex && $event.detail.targetPrefix === '{{ $prefix }}') {
            console.log('--- TRIPLE FAILSAFE SYNC ({{ $prefix }}) ---');
            const data = $event.detail;
            const idx = _stopIndex;
            const pfx = '{{ $prefix }}';

            // 1. Sync search box
            query = data.company_name || '';

            // 2. DIRECT DOM INJECTION (Bypasses all reactivity/cache issues)
            // Detail inputs use x-show (not x-if), so they stay in the DOM while collapsed.
            const fieldMap = {
                'address_1': data.address_1 || '',
                'address_2': data.address_2 || '',
                'city':      data.city || '',
                'state':     data.state || '',
                'zip':       data.zip || '',
                'country':   data.country || '',
                'lat':       data.lat ?? '',
                'lng':       data.lng ?? '',
            };

            Object.entries(fieldMap).forEach(([field, value]) => {
                const el = document.getElementById(`field-${pfx}-${field}-${idx}`);
                if (el) {
                    console.log('Injecting ' + field + ':', value);
                    el.value = value;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });

            // 3. Update background coordinates and state
            {{ $loc }}.company_name = data.company_name || '';
            {{ $loc }}.address_1    = data.address_1 || '';
            {{ $loc }}.address_2    = data.address_2 || '';
            {{ $loc }}.city         = data.city || '';
            {{ $loc }}.state        = data.state || '';
            {{ $loc }}.zip          = data.zip || '';
            {{ $loc }}.country      = data.country || '';
            {{ $loc }}.lat          = data.lat || null;
            {{ $loc }}.lng          = data.lng || null;

            // Google returns a phone for most businesses; contact name and email only come from the contact book.
            if (data.contact_name) {{ $loc }}.contact_name = data.contact_name;
            if (data.phone)        {{ $loc }}.phone        = data.phone;
            if (data.email)        {{ $loc }}.email        = data.email;

            // The customer's saved addresses carry stop defaults: hours, notes, broker and accessorials.
            if (data.source === 'customer') {
                if (data.opening_time) {{ $loc }}.opening_time = data.opening_time;
                if (data.closing_time) {{ $loc }}.closing_time = data.closing_time;
                const savedNote = '{{ $prefix }}' === 'shipper' ? data.shipper_notes : data.consignee_notes;
                if (savedNote && !{{ $loc }}.notes) {{ $loc }}.notes = savedNote;
                if (data.customs_broker && stop.billing && !stop.billing.customs_broker) stop.billing.customs_broker = data.customs_broker;
                if (Array.isArray(data.accessorial_ids)) {
                    data.accessorial_ids
                        .filter(id => accessorialsList[id] !== undefined && !stop.accessorials.includes(id))
                        .forEach(id => stop.accessorials.push(id));
                }
            }

            console.log('--- Failsafe Sync Complete ---');
        }
     ">

    {{-- Company Name with Contact Book / Google Places Autocomplete --}}
    <div class="relative" @input="{{ $loc }}.company_name = query">
        @include('livewire.places-autocomplete')
    </div>

    {{-- Address, contact & dock hours: summary row, expandable to the full editable fields --}}
    <div class="rounded-md border border-gray-200 dark:border-gray-700 transition-colors"
         :class="[showDetails ? '' : 'bg-gray-50 dark:bg-gray-800/40 hover:bg-gray-100/80 dark:hover:bg-gray-800/70', isGoogleLoading ? 'opacity-60 animate-pulse' : '']">
        <button type="button"
                @click="showDetails = !showDetails"
                :aria-expanded="showDetails"
                class="w-full flex items-start gap-3 px-3 py-2.5 text-left rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40">
            <div class="flex-1 min-w-0">
                {{-- Collapsed: address & contact preview --}}
                <template x-if="!showDetails">
                    <div class="space-y-1.5">
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <div class="min-w-0" x-show="{{ $loc }}.address_1 || {{ $loc }}.city">
                                <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate"
                                   x-text="[{{ $loc }}.address_1, {{ $loc }}.address_2].filter(Boolean).join(', ') || '—'"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                   x-text="[{{ $loc }}.city, [{{ $loc }}.state, {{ $loc }}.zip].filter(Boolean).join(' ')].filter(Boolean).join(', ') + ({{ $loc }}.country ? ' · ' + countryName({{ $loc }}.country) : '')"></p>
                            </div>
                            <div class="min-w-0" x-show="!({{ $loc }}.address_1 || {{ $loc }}.city)">
                                <p class="text-xs font-medium text-amber-700 dark:text-amber-400">No address yet</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Search above or add it manually</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <p class="text-xs text-gray-600 dark:text-gray-300 truncate"
                               x-show="{{ $loc }}.contact_name || {{ $loc }}.phone || {{ $loc }}.email"
                               x-text="[{{ $loc }}.contact_name, {{ $loc }}.phone, {{ $loc }}.email].filter(Boolean).join(' · ')"></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500"
                               x-show="!({{ $loc }}.contact_name || {{ $loc }}.phone || {{ $loc }}.email)">No contact added</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-gray-600 dark:text-gray-300 truncate"
                               x-show="{{ $loc }}.opening_time || {{ $loc }}.closing_time"
                               x-text="'Open ' + ({{ $loc }}.opening_time || '—') + ' – ' + ({{ $loc }}.closing_time || '—')"></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500"
                               x-show="!({{ $loc }}.opening_time || {{ $loc }}.closing_time)">No dock hours</p>
                        </div>
                    </div>
                </template>

                {{-- Expanded: section label --}}
                <p x-show="showDetails" class="text-xs font-medium text-gray-700 dark:text-gray-200 leading-5">Address, contact &amp; hours</p>
            </div>

            <span class="shrink-0 inline-flex items-center gap-0.5 text-xs font-medium text-gray-500 dark:text-gray-400 leading-5">
                <span x-text="showDetails ? 'Hide' : 'Edit'"></span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="showDetails ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </span>
        </button>

        {{-- Full fields (kept in the DOM so autofill can target them by id) --}}
        <div x-show="showDetails" x-collapse>
            <div class="px-3 pb-3 space-y-3">
                <div>
                    <label class="{{ $labelClass }}">Address 1</label>
                    <input type="text" :id="`field-{{ $prefix }}-address_1-${_stopIndex}`" x-model="{{ $loc }}.address_1" class="{{ $inputClass }}" placeholder="Street address">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Address 2</label>
                    <input type="text" :id="`field-{{ $prefix }}-address_2-${_stopIndex}`" x-model="{{ $loc }}.address_2" class="{{ $inputClass }}" placeholder="Suite, unit, building, floor, etc.">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="{{ $labelClass }}">City</label>
                        <input type="text" :id="`field-{{ $prefix }}-city-${_stopIndex}`" x-model="{{ $loc }}.city" class="{{ $inputClass }}" placeholder="City">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">State</label>
                        <input type="text" :id="`field-{{ $prefix }}-state-${_stopIndex}`" x-model="{{ $loc }}.state" class="{{ $inputClass }}" placeholder="ST">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Zip</label>
                        <input type="text" :id="`field-{{ $prefix }}-zip-${_stopIndex}`" x-model="{{ $loc }}.zip" class="{{ $inputClass }}" placeholder="12345">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="{{ $labelClass }}" title="2-letter country code, e.g. US, CA, PK">Country code</label>
                        <input type="text" :id="`field-{{ $prefix }}-country-${_stopIndex}`" x-model="{{ $loc }}.country" class="{{ $inputClass }}" placeholder="US">
                        <p class="mt-1 truncate text-[10px] text-gray-400 dark:text-gray-500"
                           x-show="{{ $loc }}.country && countryName({{ $loc }}.country) !== {{ $loc }}.country"
                           x-text="countryName({{ $loc }}.country)"></p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Latitude</label>
                        <input type="text" inputmode="decimal" :id="`field-{{ $prefix }}-lat-${_stopIndex}`" x-model="{{ $loc }}.lat" class="{{ $inputClass }}" placeholder="43.6532">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Longitude</label>
                        <input type="text" inputmode="decimal" :id="`field-{{ $prefix }}-lng-${_stopIndex}`" x-model="{{ $loc }}.lng" class="{{ $inputClass }}" placeholder="-79.3832">
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Contact Name</label>
                            <input type="text" x-model="{{ $loc }}.contact_name" class="{{ $inputClass }}" placeholder="Name">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Phone</label>
                            <input type="text" x-model="{{ $loc }}.phone" class="{{ $inputClass }}" placeholder="Phone">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Contact Email</label>
                        <input type="email" x-model="{{ $loc }}.email" class="{{ $inputClass }}" placeholder="email@example.com">
                    </div>
                </div>

                {{-- Dock Hours –– 24h custom picker, no AM/PM --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelClass }}">Opening</label>
                        <x-time-picker x-model="{{ $loc }}.opening_time" class="mt-1 w-full" />
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Closing</label>
                        <x-time-picker x-model="{{ $loc }}.closing_time" class="mt-1 w-full" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ready/Requested Window –– 24h custom picker, no AM/PM --}}
    <div>
        <p class="mb-2 text-[10px] font-semibold text-gray-700 dark:text-gray-300 uppercase" title="24-hour format. End must be on or after Start.">
            {{ $prefix === 'shipper' ? 'Ready Window' : 'Requested Window' }}
        </p>
        <div class="space-y-2">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-9 shrink-0 text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">Start</span>
                    <x-datetime-picker
                        x-model="{{ $loc }}.{{ $prefix === 'shipper' ? 'ready_start_at_picker' : 'requested_start_at_picker' }}"
                        @change="syncStopDateTime(stop, '{{ $prefix }}', 'start')"
                        class="flex-1 min-w-0"
                    />
                </div>
                @if($prefix === 'consignee')
                <div x-show="stop._requestedStartError" data-date-error class="{{ $dateErrorClass }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="stop._requestedStartError"></span>
                </div>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-9 shrink-0 text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">End</span>
                    <x-datetime-picker
                        x-model="{{ $loc }}.{{ $prefix === 'shipper' ? 'ready_end_at_picker' : 'requested_end_at_picker' }}"
                        @change="syncStopDateTime(stop, '{{ $prefix }}', 'end')"
                        class="flex-1 min-w-0"
                    />
                </div>
                @if($prefix === 'shipper')
                <div x-show="stop._readyEndError" data-date-error class="{{ $dateErrorClass }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="stop._readyEndError"></span>
                </div>
                @else
                <div x-show="stop._requestedEndError" data-date-error class="{{ $dateErrorClass }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="stop._requestedEndError"></span>
                </div>
                @endif
            </div>
        </div>
        <label class="inline-flex items-center gap-2 mt-3 cursor-pointer">
            <input type="checkbox" x-model="{{ $loc }}.appointment" class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 w-4 h-4">
            <span class="text-xs text-gray-700 dark:text-gray-300">Make this an appointment</span>
        </label>
    </div>

    {{-- Notes --}}
    <div>
        <label class="{{ $labelClass }}">{{ $prefix === 'shipper' ? 'Shipper Notes' : 'Consignee Notes' }}</label>
        <textarea x-model="{{ $loc }}.notes" rows="2" class="{{ $inputClass }} resize-none" placeholder="Type any notes here..."></textarea>
    </div>
</div>
