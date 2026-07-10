@php
    $activeClasses = 'text-primary-600 dark:text-white bg-primary-50 dark:bg-primary-600/10 border border-primary-100 dark:border-primary-500/20 shadow-lg shadow-primary-500/5';
    $inactiveClasses = 'text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5';
    $activeIconClasses = 'text-primary-600 dark:text-primary-500';

    $sidebarLogoLight = $company->logo_light ?? \App\Models\SystemSetting::instance()->logo_light ?? null;
    $sidebarLogoDark = $company->logo_dark ?? \App\Models\SystemSetting::instance()->logo_dark ?? null;
    $customer = auth('customer')->user();
@endphp

<aside
    class="fixed left-0 top-0 z-50 h-screen w-64 bg-white dark:bg-[#0B1120] border-r border-gray-200 dark:border-gray-800/50 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="{
        '-translate-x-full': !sidebarMobileOpen,
        'translate-x-0': sidebarMobileOpen
    }"
>
    {{-- Logo --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100 dark:border-gray-800/50 shrink-0">
        <a href="{{ route('portal.dashboard', ['company' => $company->slug]) }}" class="flex items-center gap-3 group min-w-0">
            @if($sidebarLogoLight || $sidebarLogoDark)
                @if($sidebarLogoLight)
                <img src="{{ asset('storage/' . $sidebarLogoLight) }}" alt="Logo"
                     class="h-9 max-w-[140px] object-contain dark:hidden group-hover:opacity-90 transition-opacity">
                @endif
                @if($sidebarLogoDark)
                <img src="{{ asset('storage/' . $sidebarLogoDark) }}" alt="Logo"
                     class="h-9 max-w-[140px] object-contain hidden dark:block group-hover:opacity-90 transition-opacity">
                @elseif($sidebarLogoLight)
                <img src="{{ asset('storage/' . $sidebarLogoLight) }}" alt="Logo"
                     class="h-9 max-w-[140px] object-contain hidden dark:block group-hover:opacity-90 transition-opacity">
                @endif
            @else
                <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-accent-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20 group-hover:scale-105 transition-transform duration-300 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-bold text-gray-900 dark:text-white tracking-tight leading-none truncate">{{ $company->name }}</span>
                    <span class="text-[9px] font-medium text-primary-600 dark:text-primary-400 uppercase tracking-[0.15em]">Portal</span>
                </div>
            @endif
        </a>
        <button @click="sidebarMobileOpen = false"
            class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 no-scrollbar">
        <div>
            <p class="px-4 mb-4 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.15em]">
                Main Menu
            </p>
            <div class="space-y-1.5">
                <a href="{{ route('portal.dashboard', ['company' => $company->slug]) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('portal.dashboard') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('portal.dashboard') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Dashboard</span>
                </a>

                <a href="{{ route('portal.orders.index', ['company' => $company->slug]) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('portal.orders.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('portal.orders.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">My Orders</span>
                </a>
            </div>
        </div>

        <div>
            <p class="px-4 mb-4 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.15em]">
                Account
            </p>
            <div class="space-y-1.5">
                <a href="{{ route('portal.settings', ['company' => $company->slug]) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('portal.settings*', 'portal.profile') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('portal.settings*', 'portal.profile') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Profile & Settings</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Customer profile footer --}}
    @if($customer)
    <div class="shrink-0 p-4 border-t border-gray-200 dark:border-gray-800/50 bg-white dark:bg-[#0B1120]">
        <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700/30">
            <a href="{{ route('portal.settings', ['company' => $company->slug]) }}" class="flex items-center gap-3 flex-1 min-w-0 group">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-tr from-primary-500 to-accent-600 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-primary-500/10 group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-[#0B1120] rounded-full"></div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                        {{ $customer->name }}
                    </p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-500 truncate mt-0.5">
                        {{ $customer->customer_email }}
                    </p>
                </div>
            </a>
            <form method="POST" action="{{ route('portal.logout', ['company' => $company->slug]) }}">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Sign out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    @endif
</aside>
