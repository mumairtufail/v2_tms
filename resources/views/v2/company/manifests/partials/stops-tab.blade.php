@php
    // Normalize manual stops + order stops into one list
    $allStops = [];
    foreach ($manifest->stops as $s) {
        $allStops[] = ['kind' => 'manual', 'stop' => $s];
    }
    foreach ($manifest->orderStops as $s) {
        $allStops[] = ['kind' => 'order', 'stop' => $s];
    }
@endphp

<div class="space-y-6" x-data="{ detailOpen: false, detailIndex: null, openDetail(i) { this.detailIndex = i; this.detailOpen = true; } }"
     @keydown.escape.window="detailOpen = false">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manifest Stops</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ count($allStops) }} {{ Str::plural('stop', count($allStops)) }} on this manifest</p>
        </div>
        {{-- Add Manual Stop button hidden for now
        <button @click="openStopModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Manual Stop
        </button>
        --}}
    </div>

    @if(count($allStops) > 0)
        <!-- Card View -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 items-start">
            @foreach($allStops as $i => $item)
                @php $stop = $item['stop']; @endphp
                @if($item['kind'] === 'manual')
                    <button type="button" @click="openDetail({{ $i }})"
                            class="w-full text-left bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-sm transition-all">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center text-xs font-semibold">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $stop->location }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ collect([$stop->city, $stop->state])->filter()->join(', ') ?: $stop->address1 }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-[11px] text-gray-400 dark:text-gray-500">Manual stop</p>
                    </button>
                @else
                    @php
                        $consignee = $stop->consignee_data ?? [];
                        $readyStart = $consignee['shipper_ready_start_at'] ?? ($stop->start_time ? $stop->start_time->format('m/d/y H:i') : null);
                        $commodityCount = $stop->commodities->count();
                    @endphp
                    <button type="button" @click="openDetail({{ $i }})"
                            class="w-full text-left bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-sm transition-all">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center text-xs font-semibold">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $stop->company_name ?: 'Stop' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ collect([$stop->city, $stop->state])->filter()->join(', ') ?: $stop->address_1 }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500">
                            <span>{{ $readyStart }}</span>
                            <span>{{ $commodityCount }} {{ Str::plural('item', $commodityCount) }} · #{{ $stop->order->order_number ?? '' }}</span>
                        </div>
                    </button>
                @endif
            @endforeach
        </div>

        <!-- Stop Detail Modal -->
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="detailOpen = false"></div>
            <div class="relative min-h-full flex items-start justify-center p-4 sm:p-8" @click.self="detailOpen = false">
                @foreach($allStops as $i => $item)
                    @php $stop = $item['stop']; @endphp
                    <div x-show="detailIndex === {{ $i }}"
                         class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
                        @if($item['kind'] === 'manual')
                            <!-- Manual Stop Detail -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center text-xs font-semibold">{{ $i + 1 }}</span>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $stop->location }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Manual stop</p>
                                    </div>
                                </div>
                                <button @click="detailOpen = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="px-6 py-5 space-y-4">
                                <div>
                                    <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Address</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ collect([$stop->address1, $stop->city, $stop->state, $stop->postal])->filter()->join(', ') }}</p>
                                </div>
                                <div class="flex justify-end">
                                    <form action="{{ route('v2.manifests.stops.destroy', ['company' => $company->slug, 'manifest' => $manifest->id, 'stop' => $stop->id]) }}" method="POST" onsubmit="return confirm('Remove stop?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Remove stop</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            @php
                                $consignee = $stop->consignee_data ?? [];
                                $billing = $stop->billing_data ?? [];

                                $shipperAddress = collect([$stop->address_1, $stop->city, trim(($stop->state ?? '') . ' ' . ($stop->postal_code ?? ''))])->filter()->join(', ');
                                $consigneeAddress = collect([$consignee['address_1'] ?? '', $consignee['city'] ?? '', trim(($consignee['state'] ?? '') . ' ' . ($consignee['zip'] ?? ''))])->filter()->join(', ');

                                $times = array_filter([
                                    'Ready Start' => $consignee['shipper_ready_start_at'] ?? ($stop->start_time ? $stop->start_time->format('m/d/y H:i') : null),
                                    'Ready End' => $consignee['shipper_ready_end_at'] ?? ($stop->start_time ? $stop->start_time->format('m/d/y H:i') : null),
                                    'Requested Start' => $consignee['requested_start_at'] ?? ($stop->end_time ? $stop->end_time->format('m/d/y H:i') : null),
                                    'Requested End' => $consignee['requested_end_at'] ?? ($stop->end_time ? $stop->end_time->format('m/d/y H:i') : null),
                                ]);

                                $refs = array_filter([
                                    'Container #' => $billing['container_number'] ?? null,
                                    'REF #' => $billing['ref_number'] ?? null,
                                    'PO #' => $billing['customer_po_number'] ?? null,
                                    'Currency' => $billing['currency'] ?? null,
                                ]);
                            @endphp
                            <!-- Order Stop Detail -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 shrink-0 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center text-xs font-semibold">{{ $i + 1 }}</span>
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-gray-900 dark:text-white truncate">{{ $stop->company_name ?: 'Stop' }}</h4>
                                        <a href="{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $stop->order_id]) }}" class="text-xs text-primary-600 hover:underline">
                                            Order #{{ $stop->order->order_number ?? '' }}
                                        </a>
                                    </div>
                                </div>
                                <button @click="detailOpen = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="px-6 py-5 space-y-5">
                                <!-- Shipper / Consignee -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4 space-y-1">
                                        <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1.5">Shipper</p>
                                        @if($stop->company_name)
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $stop->company_name }}</p>
                                        @endif
                                        @if($shipperAddress)
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $shipperAddress }}</p>
                                        @endif
                                        @if($stop->contact_name || $stop->contact_phone)
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ collect([$stop->contact_name, $stop->contact_phone])->filter()->join(' · ') }}</p>
                                        @endif
                                        @if($stop->contact_email)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 break-all">{{ $stop->contact_email }}</p>
                                        @endif
                                    </div>
                                    @if(array_filter($consignee ?: []))
                                        <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4 space-y-1">
                                            <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1.5">Consignee</p>
                                            @if(!empty($consignee['company_name']))
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $consignee['company_name'] }}</p>
                                            @endif
                                            @if($consigneeAddress)
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $consigneeAddress }}</p>
                                            @endif
                                            @if(!empty($consignee['contact_name']) || !empty($consignee['phone']))
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ collect([$consignee['contact_name'] ?? null, $consignee['phone'] ?? null])->filter()->join(' · ') }}</p>
                                            @endif
                                            @if(!empty($consignee['email']))
                                                <p class="text-sm text-gray-600 dark:text-gray-400 break-all">{{ $consignee['email'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Schedule -->
                                @if(count($times))
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Schedule</p>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                            @foreach($times as $label => $value)
                                                <div class="rounded-lg border border-gray-100 dark:border-gray-700 px-3 py-2">
                                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ $label }}</p>
                                                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $value }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- References -->
                                @if(count($refs))
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">References</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($refs as $label => $value)
                                                <span class="px-2.5 py-1 text-xs rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ $label }} <span class="font-medium text-gray-900 dark:text-white">{{ $value }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Commodities -->
                                @if($stop->commodities->count() > 0)
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Commodities</p>
                                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="bg-gray-50 dark:bg-gray-900/40 text-left text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                                        <th class="px-4 py-2 font-medium">Description</th>
                                                        <th class="px-4 py-2 font-medium text-right">Qty</th>
                                                        <th class="px-4 py-2 font-medium">Type</th>
                                                        <th class="px-4 py-2 font-medium text-right">Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                                    @foreach($stop->commodities as $commodity)
                                                        <tr>
                                                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ $commodity->description }}</td>
                                                            <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ $commodity->quantity }}</td>
                                                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $commodity->type }}</td>
                                                            <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ $commodity->weight }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- Accessorials -->
                                @if($stop->accessorials->count() > 0)
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Accessorials</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($stop->accessorials as $accessorial)
                                                <span class="px-2.5 py-1 text-xs rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">{{ $accessorial->name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Stops Added</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                Stops will appear here when orders are assigned to this manifest.
            </p>
            {{-- Add Manual Stop button hidden for now
            <button @click="openStopModal()" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Manual Stop
            </button>
            --}}
        </div>
    @endif
</div>
