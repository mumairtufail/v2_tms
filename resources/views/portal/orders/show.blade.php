@extends('portal.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
@php
    use App\Enums\OrderStatus;

    $cardClass = 'rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm';

    $statusCase = OrderStatus::tryFrom((string) $order->status);
    $statusLabel = $statusCase?->label() ?? ucfirst(str_replace('_', ' ', $order->status));

    $statusTone = match ($order->status) {
        'delivered' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-900/60',
        'in_transit', 'picked_up' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 ring-amber-200 dark:ring-amber-900/60',
        'booked' => 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 ring-primary-200 dark:ring-primary-900/60',
        'cancelled' => 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 ring-red-200 dark:ring-red-900/60',
        default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 ring-gray-200 dark:ring-gray-600',
    };

    // Where this shipment has got to. Cancelled is its own ending, not part of the flow.
    $journey = ['booked' => 'Booked', 'warehousing' => 'At warehouse', 'picked_up' => 'Picked up', 'in_transit' => 'In transit', 'delivered' => 'Delivered'];
    $journeyKeys = array_keys($journey);
    $currentStep = array_search($order->status, $journeyKeys, true);
    $showJourney = $currentStep !== false;

    $stops = $order->stops->sortBy('sequence_number');
    $customerCosts = $order->quote?->costs->where('category', 'customer') ?? collect();
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div>
        <a href="{{ route('portal.orders.index', ['company' => $company->slug]) }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to orders
        </a>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusTone }}">{{ $statusLabel }}</span>
                </div>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ $order->order_type_label }}
                    @if($order->ref_number) · Ref {{ $order->ref_number }} @endif
                    @if($order->created_at) · Placed {{ $order->created_at->format('M j, Y') }} @endif
                </p>
            </div>

            @if($order->status === 'draft' && $order->order_type === 'point_to_point')
            <a href="{{ route('portal.orders.edit', ['company' => $company->slug, 'order' => $order]) }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Continue editing
            </a>
            @endif
        </div>
    </div>

    {{-- Progress --}}
    @if($showJourney)
    <div class="{{ $cardClass }} px-5 py-4">
        <div class="flex items-center">
            @foreach($journey as $key => $label)
            @php $done = $loop->index <= $currentStep; @endphp
            <div class="flex flex-1 items-center {{ $loop->last ? 'flex-none' : '' }}">
                <div class="flex flex-col items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $done ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500' }}">
                        @if($done)
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        @endif
                    </span>
                    <span class="text-[11px] whitespace-nowrap {{ $done ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ $label }}</span>
                </div>
                @unless($loop->last)
                <div class="mx-2 mb-5 h-0.5 flex-1 rounded-full {{ $loop->index < $currentStep ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endunless
            </div>
            @endforeach
        </div>
    </div>
    @elseif($order->status === 'cancelled')
    <div class="rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50 dark:bg-red-900/20 px-5 py-4">
        <p class="text-sm font-semibold text-red-800 dark:text-red-300">This order was cancelled</p>
        <p class="mt-0.5 text-xs text-red-700 dark:text-red-400/90">Contact {{ $company->name }} if you think this is wrong.</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Route --}}
        <div class="{{ $cardClass }} lg:col-span-2">
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Route</h2>
                <span class="rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                    {{ $stops->count() }} {{ Str::plural('stop', $stops->count()) }}
                </span>
            </div>

            @forelse($stops as $stop)
            <div class="flex gap-3 px-5 py-4 {{ $loop->last ? '' : 'border-b border-gray-100 dark:border-gray-700/60' }}">
                <div class="flex flex-col items-center">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-[11px] font-bold text-gray-600 dark:text-gray-300">{{ $stop->sequence_number }}</span>
                    @unless($loop->last)<span class="mt-1 w-0.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></span>@endunless
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $stop->company_name ?: 'Stop ' . $stop->sequence_number }}</p>
                        <span class="rounded bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $stop->stop_type }}</span>
                    </div>

                    <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        {{ collect([$stop->address_1, $stop->address_2])->filter()->join(', ') }}<br>
                        {{ collect([$stop->city, $stop->state, $stop->postal_code, $stop->country])->filter()->join(', ') }}
                    </p>

                    @if($stop->start_time)
                    <p class="mt-1.5 inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $stop->start_time->format('M j, Y g:i A') }}@if($stop->end_time) – {{ $stop->end_time->format('g:i A') }}@endif
                    </p>
                    @endif

                    @if($stop->commodities->isNotEmpty())
                    <div class="mt-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 px-3 py-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Goods</p>
                        <ul class="mt-1 space-y-0.5">
                            @foreach($stop->commodities as $commodity)
                            <li class="text-xs text-gray-700 dark:text-gray-300">
                                {{ $commodity->quantity ? $commodity->quantity . ' × ' : '' }}{{ $commodity->description }}
                                @if($commodity->weight)<span class="text-gray-400"> · {{ number_format($commodity->weight) }} {{ $commodity->measurement_type === 'cm_kg' ? 'kg' : 'lbs' }}</span>@endif
                                @if($commodity->freight_class)<span class="text-gray-400"> · Class {{ $commodity->freight_class }}</span>@endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No stops on this order yet.</p>
            @endforelse
        </div>

        {{-- Side column --}}
        <div class="space-y-4">
            @if($customerCosts->isNotEmpty())
            <div class="{{ $cardClass }}">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Your quote</h2>
                </div>
                <div class="px-5 py-4 space-y-2">
                    @foreach($customerCosts as $cost)
                    <div class="flex items-baseline justify-between gap-3 text-sm">
                        <span class="min-w-0 text-gray-600 dark:text-gray-400 truncate">{{ $cost->description ?: $cost->type }}</span>
                        <span class="shrink-0 text-gray-900 dark:text-white tabular-nums">${{ number_format($cost->cost, 2) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-baseline justify-between gap-3 px-5 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white tabular-nums">${{ number_format($customerCosts->sum('cost'), 2) }}</span>
                </div>
            </div>
            @endif

            <div class="{{ $cardClass }}">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Details</h2>
                </div>
                <dl class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @php
                        $details = array_filter([
                            'Reference' => $order->ref_number,
                            'PO number' => $order->customer_po_number,
                            'Container' => $order->container_number,
                            'Manifest' => $order->manifest?->code,
                        ]);
                    @endphp
                    @forelse($details as $label => $value)
                    <div class="flex items-baseline justify-between gap-3 px-5 py-2.5">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-white text-right break-words">{{ $value }}</dd>
                    </div>
                    @empty
                    <p class="px-5 py-6 text-center text-xs text-gray-500 dark:text-gray-400">Nothing else recorded yet.</p>
                    @endforelse
                </dl>

                @if($order->special_instructions)
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Special instructions</p>
                    <p class="mt-1 text-xs leading-5 text-gray-700 dark:text-gray-300">{{ $order->special_instructions }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
