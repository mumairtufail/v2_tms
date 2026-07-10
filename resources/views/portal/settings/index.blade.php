@extends('portal.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile & Settings</h1>
    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage your account information and portal preferences</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Sidebar summary --}}
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-600/20 flex items-center justify-center">
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                    </span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $customer->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->customer_email }}</p>
                </div>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Short Code</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $customer->short_code ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Customer Type</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($customer->customer_type) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Freight Company</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $company->name }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        {{-- Profile form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Profile Information</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Update your contact details and billing address</p>

            <form method="POST" action="{{ route('portal.settings.profile.update', ['company' => $company->slug]) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                        <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', $customer->customer_email) }}" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('customer_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $customer->address) }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $customer->city) }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State / Province</label>
                        <input type="text" id="state" name="state" value="{{ old('state', $customer->state) }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('postal_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $customer->country) }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        @error('country')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                        <input type="text" id="currency" name="currency" value="{{ old('currency', $customer->currency ?? 'USD') }}" maxlength="3"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 uppercase"
                            oninput="this.value=this.value.toUpperCase().replace(/[^A-Z]/g,'')" />
                        @error('currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- Settings form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Portal Preferences</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Configure how your account behaves in the portal</p>

            <form method="POST" action="{{ route('portal.settings.preferences.update', ['company' => $company->slug]) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="location_sharing" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location Sharing</label>
                    <select id="location_sharing" name="location_sharing"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        @foreach(['Do not share' => 'Do not share location', 'approximate' => 'Approximate location', 'exact live location' => 'Exact live location'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('location_sharing', $customer->location_sharing) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('location_sharing')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="default_billing_option" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Billing Option</label>
                    <select id="default_billing_option" name="default_billing_option"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        @foreach(['shipper' => 'Shipper', 'consignee' => 'Consignee', 'third_party' => 'Third Party'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('default_billing_option', $customer->default_billing_option) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_billing_option')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="network_customer" value="0">
                    <input type="checkbox" id="network_customer" name="network_customer" value="1"
                        @checked(old('network_customer', $customer->network_customer))
                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500" />
                    <label for="network_customer" class="text-sm text-gray-700 dark:text-gray-300">
                        Participate in network customer program
                    </label>
                </div>
                @error('network_customer')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
