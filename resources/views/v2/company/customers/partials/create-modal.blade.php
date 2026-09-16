{{-- Create customer modal (Rose Rocket fields). Opens from the list, from ?create=1, or again after a validation error. --}}
@php
    $label = 'block text-xs font-medium text-gray-600 dark:text-gray-300';
    $input = 'mt-1 block w-full py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500';
    $reopen = $errors->any() && old('_form') === 'create-customer';
    $oldValues = $reopen ? collect(old())->except(['_token', '_form'])->all() : [];
@endphp

<x-modal name="create-customer" maxWidth="3xl" :show="request()->boolean('create') || $reopen" focusable>
    <form method="POST" action="{{ route('v2.customers.store', ['company' => $company->slug]) }}"
          x-data="customerCreateForm({
              shortCodeUrl: '{{ route('v2.customers.generate-short-code', ['company' => $company->slug]) }}',
              knownStates: @js(\App\Support\Regions::stateCodes()),
              knownCountries: @js(array_keys(\App\Support\Regions::COUNTRIES)),
              old: @js($oldValues),
          })"
          @google-place-selected.window="if ($event.detail.targetPrefix === 'customer') applyPlace($event.detail)"
          class="flex flex-col max-h-[calc(100dvh-3rem)]">
        @csrf
        <input type="hidden" name="_form" value="create-customer">
        <input type="hidden" name="lat" :value="f.lat">
        <input type="hidden" name="lng" :value="f.lng">

        <div class="shrink-0 px-4 sm:px-6 pt-4 sm:pt-5 pb-4 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create customer</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Search for the company by name or address to fill in the fields below.</p>
            </div>
            <button type="button" @click="$dispatch('close-modal', 'create-customer')" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Only the fields scroll; the header and buttons stay in view on short screens. --}}
        <div class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-5 space-y-4">
            <div class="relative" x-data="{ ...companyAutocomplete({ prefix: 'customer', stopIndex: 0, contactBookUrl: '{{ route('v2.contact-book.index', ['company' => $company->slug]) }}' }) }">
                @include('livewire.places-autocomplete', ['searchLabel' => 'Search', 'searchPlaceholder' => 'Search by company name or address, press Enter for Google'])
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <label class="{{ $label }}" for="cc-name">Organization name <span class="text-red-500">*</span></label>
                    <input id="cc-name" type="text" name="name" x-model="f.name" @change="suggestShortCode()" required class="{{ $input }}">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-short-code">Short code <span class="text-red-500">*</span></label>
                    <input id="cc-short-code" type="text" name="short_code" x-model="f.short_code" @input="shortCodeTouched = true; f.short_code = f.short_code.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 4)"
                           maxlength="4" placeholder="Suggested from the name" class="{{ $input }} font-mono uppercase placeholder:font-sans placeholder:normal-case">
                    <p class="mt-1 text-[11px] text-gray-400">4 letters or numbers. Locked once the customer is created.</p>
                    @error('short_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="field-customer-address_1-0">Address 1 <span class="text-red-500">*</span></label>
                    <input id="field-customer-address_1-0" type="text" name="address_1" x-model="f.address_1" required class="{{ $input }}">
                    @error('address_1')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="field-customer-address_2-0">Address 2</label>
                    <input id="field-customer-address_2-0" type="text" name="address_2" x-model="f.address_2" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="field-customer-city-0">City <span class="text-red-500">*</span></label>
                    <input id="field-customer-city-0" type="text" name="city" x-model="f.city" required class="{{ $input }}">
                    @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-state">Province / State <span class="text-red-500">*</span></label>
                    <select id="cc-state" name="state" x-model="f.state" required class="{{ $input }}">
                        <option value="">Select…</option>
                        <template x-for="code in extraStates" :key="code"><option :value="code" x-text="code"></option></template>
                        @foreach(\App\Support\Regions::STATES as $countryCode => $states)
                        <optgroup label="{{ \App\Support\Regions::COUNTRIES[$countryCode] }}">
                            @foreach($states as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                    @error('state')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="field-customer-zip-0">Postal <span class="text-red-500">*</span></label>
                    <input id="field-customer-zip-0" type="text" name="postal_code" x-model="f.postal_code" required class="{{ $input }}">
                    @error('postal_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-country">Country <span class="text-red-500">*</span></label>
                    <select id="cc-country" name="country" x-model="f.country" required class="{{ $input }}">
                        <option value="">Select…</option>
                        <template x-for="code in extraCountries" :key="code"><option :value="code" x-text="code"></option></template>
                        @foreach(\App\Support\Regions::COUNTRIES as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('country')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-currency">Currency <span class="text-red-500">*</span></label>
                    <select id="cc-currency" name="currency" x-model="f.currency" required class="{{ $input }}">
                        <option value="">Select…</option>
                        @foreach(\App\Models\Customer::CURRENCIES as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                        @endforeach
                    </select>
                    @error('currency')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-type">Customer type</label>
                    <select id="cc-type" name="customer_type" x-model="f.customer_type" class="{{ $input }}">
                        @foreach(\App\Models\Customer::TYPES as $value => $name)
                        <option value="{{ $value }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-quote">Default quote required</label>
                    <select id="cc-quote" name="quote_required" x-model="f.quote_required" class="{{ $input }}">
                        <option value="1">Quote required</option>
                        <option value="0">No quote required</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="cc-billing">Default billing options</label>
                    <select id="cc-billing" name="default_billing_option" x-model="f.default_billing_option" class="{{ $input }}">
                        @foreach(\App\Models\Customer::BILLING_OPTIONS as $value => $name)
                        <option value="{{ $value }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="shrink-0 px-4 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 flex justify-end gap-2 rounded-b-lg">
            <button type="button" @click="$dispatch('close-modal', 'create-customer')" class="h-9 px-3.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">Create customer</button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
function customerCreateForm(config) {
    return {
        f: Object.assign({
            name: '', short_code: '', address_1: '', address_2: '', city: '', state: '',
            postal_code: '', country: '', lat: '', lng: '', currency: '',
            customer_type: 'other', quote_required: '1', default_billing_option: 'third_party',
        }, config.old || {}),
        shortCodeTouched: Boolean(config.old && config.old.short_code),
        extraStates: [],
        extraCountries: [],

        init() {
            this.f.quote_required = String(this.f.quote_required === true || this.f.quote_required === '1' || this.f.quote_required === 1 ? '1' : (config.old && 'quote_required' in config.old ? '0' : '1'));
            this.ensureOptions();
        },

        async suggestShortCode() {
            const name = (this.f.name || '').trim();
            if (this.shortCodeTouched || name.length < 2) return;
            try {
                const response = await fetch(`${config.shortCodeUrl}?name=${encodeURIComponent(name)}`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!this.shortCodeTouched && data.short_code) this.f.short_code = data.short_code;
            } catch (e) {
                // Leave the field for the user to fill in.
            }
        },

        applyPlace(place) {
            if (!this.f.name) this.f.name = place.company_name || '';
            this.f.address_1 = place.address_1 || '';
            this.f.address_2 = place.address_2 || '';
            this.f.city = place.city || '';
            this.f.state = String(place.state || '').toUpperCase();
            this.f.postal_code = place.zip || place.postal_code || '';
            this.f.country = String(place.country || '').toUpperCase();
            this.f.lat = place.lat ?? '';
            this.f.lng = place.lng ?? '';
            this.ensureOptions();
            this.suggestShortCode();
        },

        ensureOptions() {
            if (this.f.state && !config.knownStates.includes(this.f.state) && !this.extraStates.includes(this.f.state)) this.extraStates.push(this.f.state);
            if (this.f.country && !config.knownCountries.includes(this.f.country) && !this.extraCountries.includes(this.f.country)) this.extraCountries.push(this.f.country);
        },
    };
}
</script>
@endpush
