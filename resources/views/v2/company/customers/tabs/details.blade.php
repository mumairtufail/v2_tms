{{-- Details tab --}}
@php
    $customer->loadCount('orders');
    $hasOrders = $customer->orders_count > 0;
@endphp

<div class="space-y-6">
    {{-- Credit --}}
    <div class="flex flex-wrap items-end gap-x-8 gap-y-3 pb-5 border-b border-gray-100 dark:border-gray-800">
        <div>
            <p class="{{ $label }}">Credit balance</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white tabular-nums">
                {{ $customer->credit_balance !== null ? '$ ' . number_format((float) $customer->credit_balance, 2) : '—' }}
            </p>
            <p class="text-[11px] text-gray-400">
                @if($customer->credit_balance_synced_at)
                    From QuickBooks {{ $customer->credit_balance_synced_at->diffForHumans() }}
                @elseif($customer->quickbooks_id)
                    Not refreshed yet
                @else
                    Sync to QuickBooks to track the balance
                @endif
            </p>
        </div>
        @if($canEdit && $customer->quickbooks_id)
        <form method="POST" action="{{ route('v2.customers.refresh-balance', ['company' => $company->slug, 'customer' => $customer->id]) }}">
            @csrf
            <button type="submit" class="{{ $secondaryButton }} h-8 text-xs">Refresh balance</button>
        </form>
        @endif
        <div>
            <p class="{{ $label }}">Credit limit</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white tabular-nums">
                {{ $customer->credit_limit !== null ? '$ ' . number_format((float) $customer->credit_limit, 2) : 'No limit' }}
            </p>
            @if($customer->credit_limit !== null && $customer->credit_balance !== null)
            <p class="text-[11px] {{ (float) $customer->credit_balance > (float) $customer->credit_limit ? 'text-red-600' : 'text-gray-400' }}">
                $ {{ number_format(max(0, (float) $customer->credit_limit - (float) $customer->credit_balance), 2) }} available
            </p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('v2.customers.update', ['company' => $company->slug, 'customer' => $customer->id]) }}"
          enctype="multipart/form-data" x-data="{ dirty: false, removeLogo: false, logoName: '' }"
          @input="dirty = true" @change="dirty = true">
        @csrf
        @method('PATCH')

        <fieldset @disabled(! $canEdit) class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-4 gap-y-3">
                <div class="md:col-span-2 xl:col-span-1">
                    <label class="{{ $label }}" for="d-name">Name <span class="text-red-500">*</span></label>
                    <input id="d-name" type="text" name="name" value="{{ old('name', $customer->name) }}" required class="{{ $input }}">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}">Short code</label>
                    <p class="mt-1 h-[34px] flex items-center px-3 rounded-md bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 font-mono text-sm text-gray-600 dark:text-gray-300" title="Short codes are locked because orders reference them">{{ $customer->short_code }}</p>
                </div>
                <div>
                    <label class="{{ $label }}" for="d-credit-limit">Credit limit</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-gray-400">$</span>
                        <input id="d-credit-limit" type="text" inputmode="decimal" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" placeholder="No limit" class="{{ $input }} pl-7 tabular-nums">
                    </div>
                    @error('credit_limit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="d-external-id">External ID</label>
                    <input id="d-external-id" type="text" name="external_id" value="{{ old('external_id', $customer->external_id) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">QB external ID</label>
                    <p class="mt-1 h-[34px] flex items-center px-3 rounded-md bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 font-mono text-sm text-gray-600 dark:text-gray-300">{{ $customer->quickbooks_id ?: '—' }}</p>
                </div>
                <div>
                    <label class="{{ $label }}" for="d-location">Share live location & ETA with customer</label>
                    <select id="d-location" name="location_sharing" class="{{ $input }}">
                        @foreach(\App\Models\Customer::LOCATION_SHARING as $value => $name)
                        <option value="{{ $value }}" @selected(old('location_sharing', $customer->location_sharing) === $value)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="d-currency">Currency</label>
                    <select id="d-currency" name="currency" class="{{ $input }}">
                        @foreach(\App\Models\Customer::CURRENCIES as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', $customer->currency ?: 'USD') === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="d-type">Customer type</label>
                    <select id="d-type" name="customer_type" class="{{ $input }}">
                        @foreach(\App\Models\Customer::TYPES as $value => $name)
                        <option value="{{ $value }}" @selected(old('customer_type', $customer->customer_type) === $value)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="d-quote">Default quote required</label>
                    <select id="d-quote" name="quote_required" class="{{ $input }}">
                        <option value="1" @selected((bool) old('quote_required', $customer->quote_required))>Quote required</option>
                        <option value="0" @selected(! (bool) old('quote_required', $customer->quote_required))>No quote required</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="d-billing">Default billing option</label>
                    <select id="d-billing" name="default_billing_option" class="{{ $input }}">
                        @foreach(\App\Models\Customer::BILLING_OPTIONS as $value => $name)
                        <option value="{{ $value }}" @selected(old('default_billing_option', $customer->default_billing_option) === $value)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-5 border-t border-gray-100 dark:border-gray-800">
                <x-toggle-switch name="is_active" label="Is active" description="Inactive customers can't be picked on new orders." :checked="(bool) old('is_active', $customer->is_active)" />
                <x-toggle-switch name="require_dimensions" label="Require dimensions" description="Orders need length, width and height on every commodity." :checked="(bool) old('require_dimensions', $customer->require_dimensions)" />
                <x-toggle-switch name="network_customer" label="Network customer" description="Part of the network customer program." :checked="(bool) old('network_customer', $customer->network_customer)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-5 border-t border-gray-100 dark:border-gray-800">
                <div>
                    <p class="{{ $sectionTitle }}">Logo</p>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 grid place-items-center overflow-hidden bg-gray-50 dark:bg-gray-800/50">
                            @if($customer->logoUrl())
                            <img src="{{ $customer->logoUrl() }}" alt="{{ $customer->name }} logo" class="w-full h-full object-contain" :class="removeLogo ? 'opacity-30' : ''">
                            @else
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $secondaryButton }} h-8 text-xs cursor-pointer">
                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="logoName = $event.target.files[0]?.name || ''; removeLogo = false">
                                {{ $customer->logo_path ? 'Replace logo' : 'Upload logo' }}
                            </label>
                            <p class="text-[11px] text-gray-400" x-text="logoName || 'PNG, JPG or WebP, up to 2 MB'"></p>
                            @if($customer->logo_path)
                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                                <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-3.5 h-3.5">
                                Remove logo
                            </label>
                            @endif
                            @error('logo')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <p class="{{ $sectionTitle }}">Billing address</p>
                        <a href="{{ $tabUrl('addresses') }}" class="text-xs font-medium text-primary-600 hover:underline">Manage addresses</a>
                    </div>
                    @if($billing = $customer->billingAddress)
                    <address class="mt-2 not-italic text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $billing->company_name }}<br>
                        {{ $billing->streetLine() }}<br>
                        {{ $billing->localityLine() }}
                    </address>
                    @else
                    <p class="mt-2 text-sm text-gray-500">No billing address yet. <a href="{{ $tabUrl('addresses') }}" class="text-primary-600 hover:underline">Add one</a></p>
                    @endif
                </div>
            </div>
        </fieldset>

        @if($canEdit)
        <div class="sticky bottom-3 mt-6 flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-900/95 backdrop-blur px-4 py-2.5 shadow-sm"
             x-show="dirty" x-transition.opacity x-cloak>
            <p class="text-sm text-gray-600 dark:text-gray-300">You have unsaved changes</p>
            <div class="flex gap-2">
                <a href="{{ $tabUrl('details') }}" class="{{ $secondaryButton }} h-8 text-xs">Discard</a>
                <button type="submit" class="{{ $primaryButton }} h-8 text-xs">Save changes</button>
            </div>
        </div>
        @endif
    </form>

    @if($canDelete)
    <div class="pt-5 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="{{ $sectionTitle }}">Delete customer</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($hasOrders)
                    This customer has orders, so it can't be deleted. Turn off <strong>Is active</strong> instead.
                @else
                    Removes the customer with its people, addresses and commodities.
                @endif
            </p>
        </div>
        @if($hasOrders)
        <button type="button" x-data @click="$dispatch('open-modal', 'customer-has-orders-{{ $customer->id }}')" class="h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">Delete customer</button>
        @include('v2.company.customers.partials.has-orders-modal', ['return' => 'details'])
        @else
        <button type="button" x-data @click="$dispatch('open-modal', 'delete-customer')" class="h-8 px-3 rounded-lg border border-red-200 dark:border-red-900/60 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Delete customer</button>
        <x-confirm-modal name="delete-customer" title="Delete customer">
            <p class="text-sm text-gray-600 dark:text-gray-400">Delete <strong>{{ $customer->name }}</strong>? This can't be undone.</p>
            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'delete-customer')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
                <form action="{{ route('v2.customers.destroy', ['company' => $company->slug, 'customer' => $customer->id]) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
                </form>
            </x-slot>
        </x-confirm-modal>
        @endif
    </div>
    @endif
</div>
