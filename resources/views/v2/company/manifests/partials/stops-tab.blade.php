<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manifest Stops</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage pickup and delivery locations</p>
        </div>
        <button @click="openStopModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Manual Stop
        </button>
    </div>

    @php
        // Combine direct manifest stops and order stops
        $manualStops = $manifest->stops;
        $orderStops = $manifest->orderStops; // Use the relationship we just added
        $allStopsCount = $manualStops->count() + $orderStops->count();
    @endphp

    <!-- Stops List -->
    @if($allStopsCount > 0)
        <div class="space-y-4">
            {{-- Display Manual Stops --}}
            @foreach($manifest->stops as $index => $stop)
                <div class="stop-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden border-l-4 border-l-primary-500"
                     x-data="{ expanded: false }">
                    <div @click="expanded = !expanded" class="p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-4">
                             <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white truncate">{{ $stop->location }}</h4>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Manual Stop</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                    {{ $stop->address1 }}{{ $stop->city ? ', ' . $stop->city : '' }}{{ $stop->state ? ', ' . $stop->state : '' }}
                                </p>
                            </div>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse class="border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-800/50">
                        <!-- Manual Stop Details (similar to before) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h5 class="font-semibold mb-2">Location</h5>
                                <p class="text-sm text-gray-600">{{ $stop->address1 }}, {{ $stop->city }}, {{ $stop->state }} {{ $stop->postal }}</p>
                            </div>
                            <div class="flex justify-end items-start">
                                <form action="{{ route('v2.manifests.stops.destroy', ['company' => $company->slug, 'manifest' => $manifest->id, 'stop' => $stop->id]) }}" method="POST" onsubmit="return confirm('Remove stop?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Display Order Stops --}}
            @foreach($orderStops as $index => $stop)
                <div class="stop-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden border-l-4 border-l-green-500"
                     x-data="{ expanded: false }">
                    <div @click="expanded = !expanded" class="p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center font-bold text-sm">
                                {{ $manualStops->count() + $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ $stop->company_name ?: 'Stop' }}
                                    </h4>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Order Stop</span>
                                    <a href="{{ route('v2.orders.edit', ['company' => $company->slug, 'order' => $stop->order_id]) }}" class="text-xs text-primary-600 hover:underline ml-2">
                                        View Order #{{ $stop->order->order_number ?? '' }}
                                    </a>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                    {{ $stop->address_1 }}{{ $stop->city ? ', ' . $stop->city : '' }}{{ $stop->state ? ', ' . $stop->state : '' }}
                                </p>
                            </div>
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expanded" x-collapse class="border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-800/50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h5 class="font-semibold mb-2">Location</h5>
                                <p class="text-sm text-gray-600">
                                    {{ $stop->company_name }}<br>
                                    {{ $stop->address_1 }}<br>
                                    {{ $stop->city }}, {{ $stop->state }} {{ $stop->postal_code }}<br>
                                    {{ $stop->country }}
                                </p>
                                <div class="mt-2 text-sm">
                                    <span class="font-medium">Type:</span> {{ ucfirst($stop->stop_type) }}
                                </div>
                            </div>
                            <div>
                                <h5 class="font-semibold mb-2">Contact</h5>
                                <p class="text-sm text-gray-600">
                                    {{ $stop->contact_name }}<br>
                                    {{ $stop->contact_phone }}<br>
                                    {{ $stop->contact_email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
                Add stops manually or assign orders to this manifest.
            </p>
            <button @click="openStopModal()" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Manual Stop
            </button>
        </div>
    @endif
</div>
