@extends('v2.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    use App\Enums\OrderStatus;

    $cardClass = 'rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm';
    $totalOrders = max(1, (int) $stats['orders']);

    // Statuses worth charting, in the order work actually moves
    $statusBar = collect(['draft', 'new', 'quoted', 'booked', 'in_transit', 'delivered', 'cancelled'])
        ->map(fn ($s) => [
            'key' => $s,
            'label' => OrderStatus::tryFrom($s)?->label() ?? ucfirst($s),
            'count' => (int) ($ordersByStatus[$s] ?? 0),
        ])
        ->filter(fn ($row) => $row['count'] > 0);

    $statusColour = [
        'draft' => 'bg-gray-400',
        'new' => 'bg-blue-500',
        'quoted' => 'bg-indigo-500',
        'booked' => 'bg-primary-500',
        'in_transit' => 'bg-amber-500',
        'delivered' => 'bg-emerald-600',
        'cancelled' => 'bg-red-500',
    ];
@endphp

<div class="space-y-5">
    <x-page-header title="Admin Dashboard" description="Everything across all organizations on {{ config('app.name') }}." />

    {{-- Headline numbers --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $headline = [
                [
                    'label' => 'Organizations',
                    'value' => $stats['organizations'],
                    'note' => $stats['organizations_active'] . ' active' . ($stats['organizations_new'] ? ' · ' . $stats['organizations_new'] . ' new this month' : ''),
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'url' => route('admin.companies.index'),
                ],
                [
                    'label' => 'Users',
                    'value' => $stats['users'],
                    'note' => $stats['users_active'] . ' active',
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'url' => route('admin.users.index'),
                ],
                [
                    'label' => 'Orders',
                    'value' => $stats['orders'],
                    'note' => $stats['orders_live'] . ' moving now' . ($stats['orders_new'] ? ' · ' . $stats['orders_new'] . ' new this month' : ''),
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'url' => null,
                ],
                [
                    'label' => 'Customers',
                    'value' => $stats['customers'],
                    'note' => $stats['manifests'] . ' manifests',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'url' => null,
                ],
            ];
        @endphp

        @foreach($headline as $card)
        <{{ $card['url'] ? 'a' : 'div' }} @if($card['url']) href="{{ $card['url'] }}" @endif
            class="{{ $cardClass }} p-5 {{ $card['url'] ? 'transition-colors hover:border-primary-300 dark:hover:border-primary-800' : '' }}">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                </span>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
            </div>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white tabular-nums">{{ number_format($card['value']) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 truncate">{{ $card['note'] }}</p>
        </{{ $card['url'] ? 'a' : 'div' }}>
        @endforeach
    </div>

    {{-- Orders by status --}}
    <div class="{{ $cardClass }}">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Orders by status</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Across every organization</p>
            </div>
            <span class="rounded-md bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:text-gray-300 tabular-nums">
                {{ number_format($stats['orders']) }} total
            </span>
        </div>

        @if($statusBar->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No orders yet.</p>
        @else
        <div class="p-5">
            <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                @foreach($statusBar as $row)
                <div class="{{ $statusColour[$row['key']] ?? 'bg-gray-400' }}"
                     style="width: {{ round($row['count'] / $totalOrders * 100, 2) }}%"
                     title="{{ $row['label'] }}: {{ $row['count'] }}"></div>
                @endforeach
            </div>

            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-2">
                @foreach($statusBar as $row)
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 shrink-0 rounded-full {{ $statusColour[$row['key']] ?? 'bg-gray-400' }}"></span>
                    <span class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $row['label'] }}</span>
                    <span class="ml-auto text-xs font-semibold text-gray-900 dark:text-white tabular-nums">{{ number_format($row['count']) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Organizations --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="{{ $cardClass }}">
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Busiest organizations</h3>
                <a href="{{ route('admin.companies.index') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($topCompanies as $org)
                <a href="{{ route('admin.companies.show', $org->id) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-500 dark:text-gray-400">
                        {{ strtoupper(substr($org->shortcode ?: $org->name, 0, 2)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $org->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $org->users_count }} {{ Str::plural('user', $org->users_count) }}</p>
                    </div>
                    <span class="shrink-0 text-sm font-semibold text-gray-900 dark:text-white tabular-nums">{{ number_format($org->orders_count) }}</span>
                    <span class="shrink-0 text-xs text-gray-400">orders</span>
                </a>
                @empty
                <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No organizations yet.</p>
                @endforelse
            </div>
        </div>

        <div class="{{ $cardClass }}">
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recently added</h3>
                <a href="{{ route('admin.companies.create') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">Add organization</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentCompanies as $org)
                <a href="{{ route('admin.companies.show', $org->id) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $org->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Added {{ $org->created_at?->diffForHumans() }}</p>
                    </div>
                    @if($org->is_active)
                        <span class="shrink-0 rounded-full bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300">Active</span>
                    @else
                        <span class="shrink-0 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inactive</span>
                    @endif
                </a>
                @empty
                <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No organizations yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
