@extends('portal.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="mb-6">
    <a href="{{ route('portal.orders.index', ['company' => $company->slug]) }}" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">&larr; Back to orders</a>
</div>

<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }} shipment</p>
        </div>
        <span class="inline-flex self-start px-3 py-1 text-sm font-medium rounded-full bg-primary-50 dark:bg-primary-600/10 text-primary-700 dark:text-primary-300">
            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Details</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            @if($order->ref_number)
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Reference #</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $order->ref_number }}</dd>
            </div>
            @endif
            @if($order->customer_po_number)
            <div>
                <dt class="text-gray-500 dark:text-gray-400">PO Number</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $order->customer_po_number }}</dd>
            </div>
            @endif
            @if($order->manifest)
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Manifest</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $order->manifest->code }}</dd>
            </div>
            @endif
            @if($order->special_instructions)
            <div class="sm:col-span-2">
                <dt class="text-gray-500 dark:text-gray-400">Special Instructions</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $order->special_instructions }}</dd>
            </div>
            @endif
        </dl>
    </div>

    @if($order->quote && $order->quote->costs->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quote Summary</h2>
        <ul class="space-y-3">
            @foreach($order->quote->costs as $cost)
            <li class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">{{ $cost->description ?? $cost->type }}</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ number_format($cost->cost, 2) }}</span>
            </li>
            @endforeach
        </ul>
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between font-semibold">
            <span>Total</span>
            <span>${{ number_format($order->quote->costs->sum('cost'), 2) }}</span>
        </div>
    </div>
    @endif
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Stops</h2>
    <div class="space-y-6">
        @foreach($order->stops->sortBy('sequence_number') as $stop)
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase">
                        Stop {{ $stop->sequence_number }} — {{ $stop->stop_type }}
                    </span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mt-2">{{ $stop->company_name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $stop->address_1 }}{{ $stop->address_2 ? ', '.$stop->address_2 : '' }}<br>
                        {{ $stop->city }}, {{ $stop->state }} {{ $stop->postal_code }} {{ $stop->country }}
                    </p>
                </div>
                @if($stop->start_time)
                <div class="text-right text-sm text-gray-500 dark:text-gray-400 shrink-0">
                    <div>{{ $stop->start_time->format('M j, Y g:i A') }}</div>
                    @if($stop->end_time)
                    <div>to {{ $stop->end_time->format('g:i A') }}</div>
                    @endif
                </div>
                @endif
            </div>

            @if($stop->commodities->isNotEmpty())
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Commodities</p>
                <ul class="space-y-2">
                    @foreach($stop->commodities as $commodity)
                    <li class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $commodity->description }}
                        @if($commodity->weight)
                        — {{ number_format($commodity->weight) }} lbs
                        @endif
                        @if($commodity->freight_class)
                        (Class {{ $commodity->freight_class }})
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
