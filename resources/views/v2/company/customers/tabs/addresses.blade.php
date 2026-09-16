{{-- Addresses tab: plain postal addresses, as many as the customer needs --}}
@php
    use App\Support\Regions;

    $addressesJson = $addresses->map(fn ($a) => [
        'id' => $a->id,
        'address_1' => $a->address_1,
        'address_2' => $a->address_2,
        'city' => $a->city,
        'state' => $a->state,
        'postal_code' => $a->postal_code,
        'country' => $a->country,
        'lat' => $a->lat,
        'lng' => $a->lng,
        'is_billing' => $a->is_billing,
    ])->keyBy('id');

    $oldAddress = null;
    if (old('_form') === 'address') {
        $oldAddress = array_merge(
            collect(old())->only(['address_1', 'address_2', 'city', 'state', 'postal_code', 'country', 'lat', 'lng'])->all(),
            ['id' => old('_address_id') ?: null, 'is_billing' => (bool) old('is_billing')]
        );
    }

    $iconButton = 'inline-flex items-center justify-center w-7 h-7 rounded-md transition-colors';
@endphp

<div x-data="addressManager({
        addresses: @js($addressesJson),
        storeUrl: '{{ route('v2.customers.addresses.store', ['company' => $company->slug, 'customer' => $customer->id]) }}',
        updateUrl: '{{ route('v2.customers.addresses.update', ['company' => $company->slug, 'customer' => $customer->id, 'address' => '__ID__']) }}',
        old: @js($oldAddress),
        addAnother: @js((bool) session('address_add_another')),
        knownStates: @js(Regions::stateCodes()),
        knownCountries: @js(array_keys(Regions::COUNTRIES)),
    })">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2 mb-3">
        <form method="GET" action="{{ route('v2.customers.show', ['company' => $company->slug, 'customer' => $customer->id]) }}" class="relative w-full sm:w-60">
            <input type="hidden" name="tab" value="addresses">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="q" value="{{ $search }}" placeholder="Search street or city"
                   class="w-full h-8 pl-8 pr-2 text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-lg placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500">
        </form>
        @if($canEdit)
        <button type="button" @click="openAddress(null)" class="{{ $primaryButton }} h-8 px-2.5 text-xs sm:ml-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Add address
        </button>
        @endif
    </div>

    @if($addresses->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-6 py-8 text-center">
        @if($search !== '')
        <p class="text-sm text-gray-600 dark:text-gray-300">No addresses match "{{ $search }}".</p>
        @else
        <p class="text-sm font-medium text-gray-900 dark:text-white">No addresses yet</p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add the customer's billing address and any other locations.</p>
        @endif
    </div>
    @else
    {{-- contain: keeps a wide table scrolling inside this box instead of widening the page on phones --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 [contain:inline-size_layout_paint]">
        <table class="w-full min-w-[640px] text-xs">
            <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr class="text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-3 py-2">Address</th>
                    <th class="px-2 py-2">City</th>
                    <th class="px-2 py-2">Province / State</th>
                    <th class="px-2 py-2">Postal</th>
                    <th class="px-2 py-2">Country</th>
                    <th class="px-2 py-2 w-20"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($addresses as $address)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $address->address_1 }}</span>
                            @if($address->is_billing)
                            <span class="px-1.5 py-px rounded bg-primary-50 dark:bg-primary-900/30 text-[10px] font-medium text-primary-700 dark:text-primary-300">Billing</span>
                            @endif
                        </div>
                        @if($address->address_2)<p class="text-gray-500 dark:text-gray-400">{{ $address->address_2 }}</p>@endif
                    </td>
                    <td class="px-2 py-2 text-gray-700 dark:text-gray-300">{{ $address->city }}</td>
                    <td class="px-2 py-2 text-gray-700 dark:text-gray-300">{{ $address->state ?: '—' }}</td>
                    <td class="px-2 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $address->postal_code ?: '—' }}</td>
                    <td class="px-2 py-2 text-gray-700 dark:text-gray-300">{{ $address->country ?: '—' }}</td>
                    <td class="px-2 py-1.5 text-right whitespace-nowrap">
                        @if($canEdit)
                        <button type="button" @click="openAddress({{ $address->id }})" class="{{ $iconButton }} text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20" title="Edit address">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        @if($address->is_billing)
                        <span class="{{ $iconButton }} text-gray-200 dark:text-gray-700 cursor-not-allowed" title="The billing address can't be deleted. Make another address the billing address first.">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </span>
                        @else
                        <button type="button" @click="askDelete('{{ route('v2.customers.addresses.destroy', ['company' => $company->slug, 'customer' => $customer->id, 'address' => $address->id]) }}', @js($address->streetLine()))"
                                class="{{ $iconButton }} text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete address">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-[11px] text-gray-400">{{ $addresses->count() === 1 ? '1 address' : $addresses->count() . ' addresses' }} · Listed first when picking a shipper or consignee on this customer's orders.</p>
    @endif

    @if($canEdit)
    @include('v2.company.customers.partials.confirm-delete-modal', ['name' => 'confirm-address-delete'])

    <x-modal name="address-modal" maxWidth="2xl" focusable>
        <form method="POST" :action="action" class="flex flex-col max-h-[calc(100dvh-3rem)]"
              @google-place-selected.window="if ($event.detail.targetPrefix === 'address') applyPlace($event.detail)">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="address">
            <input type="hidden" name="_address_id" :value="form.id || ''">
            <input type="hidden" name="lat" :value="form.lat ?? ''">
            <input type="hidden" name="lng" :value="form.lng ?? ''">

            <div class="shrink-0 px-5 pt-4 pb-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white" x-text="form.id ? 'Edit address' : 'Add address'"></h2>
                <button type="button" @click="$dispatch('close-modal', 'address-modal')" class="p-1 text-gray-400 hover:text-gray-600 rounded" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto px-5 py-4 space-y-3">
                <div class="relative" x-data="{ ...companyAutocomplete({ prefix: 'address', stopIndex: 0, contactBookUrl: '{{ route('v2.contact-book.index', ['company' => $company->slug]) }}' }) }">
                    @include('livewire.places-autocomplete', ['searchLabel' => 'Search for a location', 'searchPlaceholder' => 'Type an address, press Enter for Google'])
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-3 gap-y-3">
                    <div>
                        <label class="{{ $label }}" for="addr-1">Address 1 <span class="text-red-500">*</span></label>
                        <input id="field-address-address_1-0" type="text" name="address_1" x-model="form.address_1" required class="{{ $input }}">
                        @error('address_1')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="field-address-address_2-0">Address 2</label>
                        <input id="field-address-address_2-0" type="text" name="address_2" x-model="form.address_2" placeholder="Suite, unit, floor" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="field-address-city-0">City <span class="text-red-500">*</span></label>
                        <input id="field-address-city-0" type="text" name="city" x-model="form.city" required class="{{ $input }}">
                        @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="addr-state">Province / State <span class="text-red-500">*</span></label>
                        <select id="addr-state" name="state" x-model="form.state" required class="{{ $input }}">
                            <option value="">Select…</option>
                            <template x-for="code in extraStates" :key="code"><option :value="code" x-text="code"></option></template>
                            @foreach(Regions::STATES as $countryCode => $states)
                            <optgroup label="{{ Regions::COUNTRIES[$countryCode] }}">
                                @foreach($states as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        @error('state')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="field-address-zip-0">Postal code <span class="text-red-500">*</span></label>
                        <input id="field-address-zip-0" type="text" name="postal_code" x-model="form.postal_code" required class="{{ $input }}">
                        @error('postal_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="addr-country">Country <span class="text-red-500">*</span></label>
                        <select id="addr-country" name="country" x-model="form.country" required class="{{ $input }}">
                            <option value="">Select…</option>
                            <template x-for="code in extraCountries" :key="code"><option :value="code" x-text="code"></option></template>
                            @foreach(Regions::COUNTRIES as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('country')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 pt-1 cursor-pointer" :class="form.id && original.is_billing ? 'opacity-60 cursor-not-allowed' : ''">
                    <input type="checkbox" name="is_billing" value="1" x-model="form.is_billing" :disabled="form.id && original.is_billing"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Billing address</span>
                    <span x-show="form.id && original.is_billing" class="text-[11px] text-gray-400">Choose another address as billing to change this</span>
                </label>
                <template x-if="form.id && original.is_billing">
                    <input type="hidden" name="is_billing" value="1">
                </template>
            </div>

            <div class="shrink-0 px-5 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 flex flex-wrap justify-end gap-2 rounded-b-lg">
                <button type="button" @click="$dispatch('close-modal', 'address-modal')" class="{{ $secondaryButton }} h-8 px-3 text-xs">Cancel</button>
                <button type="submit" name="add_another" value="1" x-show="!form.id" class="{{ $secondaryButton }} h-8 px-3 text-xs">Save & add another</button>
                <button type="submit" class="{{ $primaryButton }} h-8 px-3 text-xs" x-text="form.id ? 'Save' : 'Add address'"></button>
            </div>
        </form>
    </x-modal>
    @endif
</div>

@push('scripts')
<script>
function addressManager(config) {
    const blank = () => ({
        id: null, address_1: '', address_2: '', city: '', state: '', postal_code: '', country: '',
        lat: '', lng: '', is_billing: false,
    });

    return {
        form: blank(),
        original: {},
        extraStates: [],
        extraCountries: [],
        confirmAction: '',
        confirmTitle: '',
        confirmBody: '',
        confirmIds: [],

        askDelete(action, street) {
            this.confirmAction = action;
            this.confirmTitle = 'Delete address';
            this.confirmBody = `Delete “${street}”? This can't be undone.`;
            this.$dispatch('open-modal', 'confirm-address-delete');
        },

        init() {
            if (config.old) {
                this.form = Object.assign(blank(), config.old);
                this.original = config.old.id ? (config.addresses[config.old.id] || {}) : {};
                this.ensureOptions();
                this.$nextTick(() => this.$dispatch('open-modal', 'address-modal'));
            } else if (config.addAnother) {
                this.$nextTick(() => this.openAddress(null));
            }
        },

        get action() {
            return this.form.id ? config.updateUrl.replace('__ID__', this.form.id) : config.storeUrl;
        },

        openAddress(id) {
            const address = id ? config.addresses[id] : null;
            this.form = address ? JSON.parse(JSON.stringify(address)) : blank();
            this.original = address || {};
            this.ensureOptions();
            this.$dispatch('open-modal', 'address-modal');
            setTimeout(() => document.getElementById('field-address-address_1-0')?.focus(), 150);
        },

        applyPlace(place) {
            this.form.address_1 = place.address_1 || '';
            this.form.address_2 = place.address_2 || '';
            this.form.city = place.city || '';
            this.form.state = String(place.state || '').toUpperCase();
            this.form.postal_code = place.zip || place.postal_code || '';
            this.form.country = String(place.country || '').toUpperCase();
            this.form.lat = place.lat ?? '';
            this.form.lng = place.lng ?? '';
            this.ensureOptions();
        },

        ensureOptions() {
            if (this.form.state && !config.knownStates.includes(this.form.state) && !this.extraStates.includes(this.form.state)) this.extraStates.push(this.form.state);
            if (this.form.country && !config.knownCountries.includes(this.form.country) && !this.extraCountries.includes(this.form.country)) this.extraCountries.push(this.form.country);
        },
    };
}
</script>
@endpush
