<div class="space-y-6">
    <!-- Drivers Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Assigned Drivers</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $manifest->drivers->count() }} driver(s) currently assigned</p>
                </div>
            </div>
            <button @click="openResourceModal('driver')" 
                    class="px-5 py-2.5 bg-primary-50 hover:bg-primary-100 text-primary-600 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 rounded-xl transition-all font-bold flex items-center gap-2 border border-primary-200 dark:border-primary-800 shadow-sm active:scale-95">
                <div x-show="loadingDriver" class="animate-spin h-4 w-4 border-2 border-primary-600 border-t-transparent rounded-full"></div>
                <svg x-show="!loadingDriver" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Manage
            </button>
        </div>
        <div class="p-6">
            @if($manifest->drivers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($manifest->drivers as $driver)
                        <div class="group flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-primary-500/50 transition-all hover:shadow-lg hover:shadow-primary-500/5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center font-black text-lg shadow-sm group-hover:scale-110 transition-transform">
                                    {{ strtoupper(substr($driver->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $driver->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $driver->email ?? '' }}</p>
                                </div>
                            </div>
                            <form action="{{ route('v2.manifests.drivers.destroy', ['company' => $company->slug, 'manifest' => $manifest->id, 'driver' => $driver->id]) }}" 
                                  method="POST" 
                                  x-data="{ deleting: false }" 
                                  @submit="deleting = true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        :disabled="deleting"
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl transition-all" title="Remove driver">
                                    <svg x-show="!deleting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <div x-show="deleting" class="animate-spin h-5 w-5 border-2 border-red-500 border-t-transparent rounded-full"></div>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-gray-50/30 dark:bg-gray-900/10 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h4 class="text-gray-900 dark:text-white font-bold text-lg mb-2">No drivers assigned</h4>
                    <p class="text-gray-500 dark:text-gray-400 max-w-xs mx-auto mb-8">Click manage to assign drivers to this manifest to begin tracking.</p>
                    <button @click="openResourceModal('driver')" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-all shadow-xl shadow-primary-500/25 font-bold transform active:scale-95">
                        Assign Resources
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Equipment Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-accent-100 dark:bg-accent-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Assigned Equipment</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $manifest->equipments->count() }} unit(s) assigned</p>
                </div>
            </div>
            <button @click="openResourceModal('equipment')" 
                    class="px-5 py-2.5 bg-accent-50 hover:bg-accent-100 text-accent-600 dark:bg-accent-900/20 dark:hover:bg-accent-900/30 rounded-xl transition-all font-bold flex items-center gap-2 border border-accent-200 dark:border-accent-800 shadow-sm active:scale-95">
                <div x-show="loadingEquipment" class="animate-spin h-4 w-4 border-2 border-accent-600 border-t-transparent rounded-full"></div>
                <svg x-show="!loadingEquipment" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Manage
            </button>
        </div>
        <div class="p-6">
            @if($manifest->equipments->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($manifest->equipments as $eq)
                        <div class="group flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-accent-500/50 transition-all hover:shadow-lg hover:shadow-accent-500/5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent-400 to-accent-600 text-white flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $eq->name ?? $eq->unit_number ?? 'Equipment' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $eq->type ?? 'N/A' }} · {{ $eq->status ?? 'AVL' }}</p>
                                </div>
                            </div>
                            <form action="{{ route('v2.manifests.equipment.destroy', ['company' => $company->slug, 'manifest' => $manifest->id, 'equipment' => $eq->id]) }}" 
                                  method="POST"
                                  x-data="{ deleting: false }"
                                  @submit="deleting = true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        :disabled="deleting"
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl transition-all" title="Remove equipment">
                                    <svg x-show="!deleting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <div x-show="deleting" class="animate-spin h-5 w-5 border-2 border-red-500 border-t-transparent rounded-full"></div>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-gray-50/30 dark:bg-gray-900/10 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <h4 class="text-gray-900 dark:text-white font-bold text-lg mb-2">No equipment assigned</h4>
                    <p class="text-gray-500 dark:text-gray-400 max-w-xs mx-auto mb-8">Click manage to assign equipment units to this manifest.</p>
                    <button @click="openResourceModal('equipment')" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-all shadow-xl shadow-primary-500/25 font-bold transform active:scale-95">
                        Assign Resources
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Carriers Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Assigned Carriers</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $manifest->carriers->count() }} unit(s) assigned</p>
                </div>
            </div>
            <button @click="openResourceModal('carrier')" 
                    class="px-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-xl transition-all font-bold flex items-center gap-2 border border-blue-200 dark:border-blue-800 shadow-sm active:scale-95">
                <div x-show="loadingCarrier" class="animate-spin h-4 w-4 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                <svg x-show="!loadingCarrier" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Manage
            </button>
        </div>
        <div class="p-6">
            @if($manifest->carriers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($manifest->carriers as $carrier)
                        <div class="group flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-blue-500/50 transition-all hover:shadow-lg hover:shadow-blue-500/5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $carrier->carrier_name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">DOT: {{ $carrier->dot_id ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <form action="{{ route('v2.manifests.carriers.destroy', ['company' => $company->slug, 'manifest' => $manifest->id, 'carrier' => $carrier->id]) }}" 
                                  method="POST"
                                  x-data="{ deleting: false }"
                                  @submit="deleting = true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        :disabled="deleting"
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl transition-all" title="Remove carrier">
                                    <svg x-show="!deleting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <div x-show="deleting" class="animate-spin h-5 w-5 border-2 border-red-500 border-t-transparent rounded-full"></div>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-gray-50/30 dark:bg-gray-900/10 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h4 class="text-gray-900 dark:text-white font-bold text-lg mb-2">No carriers assigned</h4>
                    <p class="text-gray-500 dark:text-gray-400 max-w-xs mx-auto mb-8">Click manage to assign carriers to this manifest.</p>
                    <button @click="openResourceModal('carrier')" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-all shadow-xl shadow-primary-500/25 font-bold transform active:scale-95">
                        Assign Resources
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
