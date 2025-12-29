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
            Add Stop
        </button>
    </div>

    <!-- Stops List -->
    @if($manifest->stops->count() > 0)
        <div class="space-y-4">
            @foreach($manifest->stops as $index => $stop)
                <div class="stop-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
                     x-data="{ expanded: false }">
                    <!-- Collapsed View -->
                    <div @click="expanded = !expanded" class="p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Sequence Badge -->
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            
                            <!-- Stop Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white truncate">{{ $stop->location }}</h4>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Active
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                    {{ $stop->address1 }}{{ $stop->city ? ', ' . $stop->city : '' }}{{ $stop->state ? ', ' . $stop->state : '' }} {{ $stop->postal }}
                                </p>
                            </div>
                            
                            <!-- Expand Icon -->
                            <svg :class="{ 'rotate-180': expanded }" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Expanded View -->
                    <div x-show="expanded" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                        <div class="p-6 bg-gray-50 dark:bg-gray-800/50">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Location Details -->
                                <div class="space-y-4">
                                    <h5 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        Location
                                    </h5>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Name:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->location }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Company:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->company ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Address:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->address1 }}</span>
                                        </div>
                                        @if($stop->address2)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Address 2:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->address2 }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- City/State Details -->
                                <div class="space-y-4">
                                    <h5 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-4 h-4 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                        </svg>
                                        Region
                                    </h5>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">City:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->city ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">State:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->state ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Postal Code:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->postal ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Country:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">{{ $stop->country ?? 'USA' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-col justify-between">
                                    <div class="space-y-2">
                                        <h5 class="font-semibold text-gray-900 dark:text-white">Actions</h5>
                                    </div>
                                    <div class="flex gap-2 mt-4">
                                        <form action="{{ route('v2.manifests.stops.destroy', ['company' => $company->slug, 'manifest' => $manifest->id, 'stop' => $stop->id]) }}" 
                                              method="POST" 
                                              x-data="{ deleting: false }"
                                              @submit="return confirm('Are you sure you want to remove this stop?') ? (deleting = true) : false">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    :disabled="deleting"
                                                    class="px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50">
                                                <svg x-show="!deleting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <svg x-show="deleting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span x-text="deleting ? 'Removing...' : 'Remove Stop'"></span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
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
                Add stops to define the route for this manifest. Each stop represents a pickup or delivery location.
            </p>
            <button @click="openStopModal()" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add First Stop
            </button>
        </div>
    @endif
</div>
