@extends('v2.layouts.app')

@section('title', $customer->name . ' · Customers')

@section('content')
@php
    $canEdit = auth()->user()->hasPermission('customers', 'update');
    $canDelete = auth()->user()->hasPermission('customers', 'delete');
    $tabUrl = fn (string $name, array $extra = []) => route('v2.customers.show', array_merge(['company' => $company->slug, 'customer' => $customer->id, 'tab' => $name], $extra));

    // Heroicons (outline) paths per section
    $tabs = [
        'details' => [
            'label' => 'Details',
            'description' => 'Company profile, credit and order defaults.',
            'count' => null,
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'people' => [
            'label' => 'People',
            'description' => 'Contacts at ' . $customer->name . ', their portal access and the updates they get.',
            'count' => $customer->contacts_count,
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        ],
        'addresses' => [
            'label' => 'Addresses',
            'description' => 'Pickup and delivery locations. They show first when this customer\'s orders pick a shipper or consignee.',
            'count' => $customer->addresses_count,
            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        'accounting' => [
            'label' => 'Accounting',
            'description' => 'Invoice terms, recipients and documents.',
            'count' => null,
            'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        ],
        'accessorials' => [
            'label' => 'Accessorials',
            'description' => 'Which accessorials this customer\'s orders can use.',
            'count' => null,
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        ],
        'commodities' => [
            'label' => 'Commodities',
            'description' => 'Saved commodity presets for this customer\'s orders.',
            'count' => $customer->commodities_count,
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        ],
    ];

    // Shared field styles for the tab partials
    $label = 'block text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase';
    $input = 'mt-1 block w-full py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:disabled:bg-gray-800/60';
    $sectionTitle = 'text-sm font-semibold text-gray-900 dark:text-white';
    $primaryButton = 'inline-flex items-center justify-center gap-1.5 h-9 px-3.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors disabled:opacity-50';
    $secondaryButton = 'inline-flex items-center justify-center gap-1.5 h-9 px-3.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors';
@endphp

<div class="space-y-4">
    <x-v2-breadcrumb :items="[
        ['label' => 'Customers', 'url' => route('v2.customers.index', ['company' => $company->slug])],
        ['label' => $customer->name],
    ]" />

    <div class="flex flex-col lg:flex-row gap-4 lg:items-start">
        {{-- Customer card + section navigation --}}
        <aside class="w-full min-w-0 lg:w-64 shrink-0 space-y-3 lg:sticky lg:top-24">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                <div class="flex items-start gap-3">
                    @if($customer->logoUrl())
                    <img src="{{ $customer->logoUrl() }}" alt="" class="w-11 h-11 rounded-full object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                    @else
                    <span class="w-11 h-11 rounded-full shrink-0 grid place-items-center text-sm font-bold text-white" style="background-color: {{ $customer->avatarColor() }}">{{ $customer->initials() }}</span>
                    @endif
                    <div class="min-w-0">
                        <h1 class="text-base font-semibold leading-snug text-gray-900 dark:text-white break-words">{{ $customer->name }}</h1>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-mono text-[11px] text-gray-600 dark:text-gray-300">{{ $customer->short_code }}</span>
                            @if($customer->is_active)
                            <span class="px-2 py-0.5 text-[11px] rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                            @else
                            <span class="px-2 py-0.5 text-[11px] rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="mt-4 space-y-1.5 text-xs">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Type</dt>
                        <dd class="text-gray-800 dark:text-gray-200">{{ \App\Models\Customer::TYPES[$customer->customer_type] ?? 'Other' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Currency</dt>
                        <dd class="text-gray-800 dark:text-gray-200">{{ $customer->currency ?: '—' }}</dd>
                    </div>
                    @if($customer->billingAddress)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400 shrink-0">Billing</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-right truncate" title="{{ $customer->billingAddress->streetLine() }}, {{ $customer->billingAddress->localityLine() }}">{{ $customer->billingAddress->localityLine() }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">QuickBooks</dt>
                        <dd class="text-gray-800 dark:text-gray-200">
                            @if($customer->quickbooks_id)
                            <span class="font-mono">{{ $customer->quickbooks_id }}</span>
                            @else
                            Not synced
                            @endif
                        </dd>
                    </div>
                </dl>

                @if($canEdit && ! $customer->quickbooks_id)
                <form method="POST" action="{{ route('v2.customers.sync-quickbooks', ['company' => $company->slug, 'customer' => $customer->id]) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="{{ $secondaryButton }} h-8 w-full text-xs">Sync to QuickBooks</button>
                </form>
                @endif
            </div>

            {{-- On small screens the sections become a row that scrolls inside the card --}}
            <nav class="min-w-0 max-w-full bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-1.5 flex lg:flex-col gap-0.5 overflow-x-auto" aria-label="Customer sections">
                @foreach($tabs as $key => $meta)
                <a href="{{ $tabUrl($key) }}" @if($tab === $key) aria-current="page" @endif
                   class="group shrink-0 flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm whitespace-nowrap transition-colors {{ $tab === $key
                       ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-medium'
                       : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $tab === $key ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $meta['icon'] }}"/>
                    </svg>
                    <span class="lg:flex-1">{{ $meta['label'] }}</span>
                    @if($meta['count'])
                    <span class="px-1.5 rounded-full text-[10px] font-medium tabular-nums {{ $tab === $key ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">{{ $meta['count'] }}</span>
                    @endif
                </a>
                @endforeach
            </nav>
        </aside>

        {{-- Section content --}}
        <section class="w-full flex-1 min-w-0 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
            <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $tabs[$tab]['label'] }}</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $tabs[$tab]['description'] }}</p>
            </header>
            <div class="p-5">
                @include('v2.company.customers.tabs.' . $tab)
            </div>
        </section>
    </div>
</div>
@endsection
