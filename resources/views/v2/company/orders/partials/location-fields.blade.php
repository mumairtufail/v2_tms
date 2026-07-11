{{-- Location Fields Partial for Order Form --}}
{{-- $prefix: 'shipper' or 'consignee' --}}

<div class="grid grid-cols-1 gap-4 p-4 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 shadow-sm"
     x-data="{
        ...companyAutocomplete({
            initialQuery: stop.{{ $prefix }}.company_name,
            prefix: '{{ $prefix }}',
            stopIndex: stopIndex,
            contactBookUrl: '{{ route('v2.contact-book.index', ['company' => $company->slug]) }}'
        }),
        _stopIndex: stopIndex
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
            stop.{{ $prefix }}.company_name = data.company_name || '';
            stop.{{ $prefix }}.address_1    = data.address_1 || '';
            stop.{{ $prefix }}.address_2    = data.address_2 || '';
            stop.{{ $prefix }}.city         = data.city || '';
            stop.{{ $prefix }}.state        = data.state || '';
            stop.{{ $prefix }}.zip          = data.zip || '';
            stop.{{ $prefix }}.country      = data.country || '';
            stop.{{ $prefix }}.lat          = data.lat || null;
            stop.{{ $prefix }}.lng          = data.lng || null;

            // Contact book entries may carry contact details; Google results never do.
            if (data.contact_name) stop.{{ $prefix }}.contact_name = data.contact_name;
            if (data.phone)        stop.{{ $prefix }}.phone        = data.phone;
            if (data.email)        stop.{{ $prefix }}.email        = data.email;

            console.log('--- Failsafe Sync Complete ---');
        }
     ">

    {{-- Row 1: Company Name with Google Places Autocomplete --}}
    <div class="relative" @input="stop.{{ $prefix }}.company_name = query">
        @include('livewire.places-autocomplete')
    </div>

    {{-- Row 2: Address 1 --}}
    <div :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Address 1</label>
        <input type="text"
               :id="`field-{{ $prefix }}-address_1-${_stopIndex}`"
               x-model="stop.{{ $prefix }}.address_1"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Street address">
    </div>

    {{-- Row 3: Address 2 --}}
    <div :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Address 2</label>
        <input type="text"
               :id="`field-{{ $prefix }}-address_2-${_stopIndex}`"
               x-model="stop.{{ $prefix }}.address_2"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Suite, unit, building, floor, etc.">
    </div>

    {{-- Row 4: City, State, Zip --}}
    <div class="grid grid-cols-3 gap-3" :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">City</label>
            <input type="text"
                   :id="`field-{{ $prefix }}-city-${_stopIndex}`"
                   x-model="stop.{{ $prefix }}.city"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="City">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">State</label>
            <input type="text"
                   :id="`field-{{ $prefix }}-state-${_stopIndex}`"
                   x-model="stop.{{ $prefix }}.state"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="ST">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Zip</label>
            <input type="text"
                   :id="`field-{{ $prefix }}-zip-${_stopIndex}`"
                   x-model="stop.{{ $prefix }}.zip"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="12345">
        </div>
    </div>

    {{-- Row 5: Country --}}
    <div :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Country</label>
        <input type="text"
               :id="`field-{{ $prefix }}-country-${_stopIndex}`"
               x-model="stop.{{ $prefix }}.country"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Country (e.g. US, DE)">
    </div>

    {{-- Row 5b: Latitude & Longitude (auto-filled from Google, manually editable) --}}
    <div class="grid grid-cols-2 gap-3" :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Latitude</label>
            <input type="text" inputmode="decimal"
                   :id="`field-{{ $prefix }}-lat-${_stopIndex}`"
                   x-model="stop.{{ $prefix }}.lat"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="e.g. 43.6532">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Longitude</label>
            <input type="text" inputmode="decimal"
                   :id="`field-{{ $prefix }}-lng-${_stopIndex}`"
                   x-model="stop.{{ $prefix }}.lng"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="e.g. -79.3832">
        </div>
    </div>

    {{-- Row 6: Contact Name & Phone --}}
    <div class="grid grid-cols-2 gap-3" :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Contact Name</label>
            <input type="text"
                   x-model="stop.{{ $prefix }}.contact_name"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Phone</label>
            <input type="text"
                   x-model="stop.{{ $prefix }}.phone"
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
        </div>
    </div>

    {{-- Row 7: Contact Email --}}
    <div :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Contact Email</label>
        <input type="email"
               x-model="stop.{{ $prefix }}.email"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="email@example.com">
    </div>

    {{-- Row 8: Opening & Closing Times –– 24h custom picker, no AM/PM --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Opening</label>
            <x-time-picker x-model="stop.{{ $prefix }}.opening_time" class="mt-0.5 w-full" />
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Closing</label>
            <x-time-picker x-model="stop.{{ $prefix }}.closing_time" class="mt-0.5 w-full" />
        </div>
    </div>

    {{-- Row 9: Ready/Requested Date & Time –– 24h custom picker, no AM/PM --}}
    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">
            {{ $prefix === 'shipper' ? 'Ready Date (Pickup Window)' : 'Requested Date (Delivery Window)' }}
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase">Start</label>
                <x-datetime-picker
                    x-model="stop.{{ $prefix }}.{{ $prefix === 'shipper' ? 'ready_start_at_picker' : 'requested_start_at_picker' }}"
                    @change="syncStopDateTime(stop, '{{ $prefix }}', 'start')"
                    class="mt-0.5 w-full"
                />
                @if($prefix === 'consignee')
                <div x-show="stop._requestedStartError" data-date-error
                     class="date-error-msg mt-1.5 flex items-center gap-1.5 text-[11px] text-red-600 dark:text-red-400 font-medium bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-2.5 py-1.5 rounded-md">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="stop._requestedStartError"></span>
                </div>
                @endif
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase">End</label>
                <x-datetime-picker
                    x-model="stop.{{ $prefix }}.{{ $prefix === 'shipper' ? 'ready_end_at_picker' : 'requested_end_at_picker' }}"
                    @change="syncStopDateTime(stop, '{{ $prefix }}', 'end')"
                    class="mt-0.5 w-full"
                />
                @if($prefix === 'shipper')
                <div x-show="stop._readyEndError" data-date-error
                     class="date-error-msg mt-1.5 flex items-center gap-1.5 text-[11px] text-red-600 dark:text-red-400 font-medium bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-2.5 py-1.5 rounded-md">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="stop._readyEndError"></span>
                </div>
                @else
                <div x-show="stop._requestedEndError" data-date-error
                     class="date-error-msg mt-1.5 flex items-center gap-1.5 text-[11px] text-red-600 dark:text-red-400 font-medium bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-2.5 py-1.5 rounded-md">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="stop._requestedEndError"></span>
                </div>
                @endif
            </div>
        </div>
        <p class="text-[10px] text-gray-400 mt-1">24-hour format. End must be on or after Start.</p>
        <label class="flex items-center gap-2 mt-2 cursor-pointer">
            <input type="checkbox" x-model="stop.{{ $prefix }}.appointment" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 w-4 h-4">
            <span class="text-xs text-gray-600 dark:text-gray-400">Make this an appointment</span>
        </label>
    </div>

    {{-- Row 10: Notes --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">{{ $prefix === 'shipper' ? 'Shipper Notes' : 'Consignee Notes' }}</label>
        <textarea x-model="stop.{{ $prefix }}.notes" rows="2" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500" placeholder="Type any notes here..."></textarea>
    </div>
</div>
