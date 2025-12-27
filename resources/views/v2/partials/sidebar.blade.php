<!-- Sidebar -->
@php
    $currentCompany = app()->bound('current.company') ? app('current.company') : null;
    $companySlug = $currentCompany?->slug ?? 'system-administration';
    
    // Active link classes using centralized primary colors
    $activeClasses = 'text-primary-600 dark:text-white bg-primary-50 dark:bg-primary-600/10 border border-primary-100 dark:border-primary-500/20 shadow-lg shadow-primary-500/5';
    $inactiveClasses = 'text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5';
    $activeIconClasses = 'text-primary-600 dark:text-primary-500';
@endphp
<aside
    class="fixed left-0 top-0 z-40 h-screen w-64 bg-white dark:bg-[#0B1120] border-r border-gray-200 dark:border-gray-800/50
           transition-transform duration-300 ease-in-out
           lg:translate-x-0"
    :class="{
        '-translate-x-full': !sidebarMobileOpen,
        'translate-x-0': sidebarMobileOpen
    }"
>
    <!-- Logo Section -->
    <div class="h-20 flex items-center px-6 mb-4">
        <a href="{{ $currentCompany ? route('v2.dashboard', ['company' => $companySlug]) : route('admin.dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20 group-hover:scale-105 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold text-gray-900 dark:text-white tracking-tight leading-none">TMS</span>
                <span class="text-[10px] font-medium text-primary-600 dark:text-primary-400 uppercase tracking-[0.2em] mt-1">Pro</span>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-4 space-y-8 no-scrollbar h-[calc(100vh-180px)]">
        
        <!-- Main Section -->
        <div>
            <p class="px-4 mb-4 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.15em]">
                Main Menu
            </p>

            <div class="space-y-1.5">
                <!-- Dashboard -->
                <a href="{{ $currentCompany ? route('v2.dashboard', ['company' => $companySlug]) : route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('v2.dashboard*') || request()->routeIs('admin.dashboard') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('v2.dashboard*') || request()->routeIs('admin.dashboard') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Dashboard</span>
                </a>

                <!-- Orders -->
                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('v2.orders.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('v2.orders.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Orders</span>
                </a>

                <!-- Manifests -->
                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('v2.manifests.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('v2.manifests.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Manifests</span>
                </a>

                <!-- Customers -->
                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('v2.customers.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('v2.customers.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Customers</span>
                </a>

                <!-- Users -->
                <a href="{{ $currentCompany ? route('v2.users.index', ['company' => $companySlug]) : '#' }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('v2.users.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('v2.users.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Users</span>
                </a>
            </div>
        </div>

        @if(auth()->user()->is_super_admin)
        <!-- System Admin Section -->
        <div>
            <p class="px-4 mb-4 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.15em]">
                System Admin
            </p>

            <div class="space-y-1.5">
                <!-- Companies -->
                <a href="{{ route('admin.companies.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.companies.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('admin.companies.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Companies</span>
                </a>

                <!-- Admin Users -->
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.users.*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('admin.users.*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">All Users</span>
                </a>

                <!-- Global Logs -->
                <a href="{{ route('admin.logs') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.logs*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('admin.logs*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Global Logs</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Management Section -->
        <div>
            <p class="px-4 mb-4 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.15em]">
                Management
            </p>

            <div class="space-y-1.5">
                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->routeIs('v2.settings*') ? $activeClasses : $inactiveClasses }}">
                    <div class="w-5 h-5 flex items-center justify-center {{ request()->routeIs('v2.settings*') ? $activeIconClasses : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-[14px]">Settings</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- User Profile Section -->
    <div class="absolute bottom-0 left-0 w-full p-4 bg-white dark:bg-[#0B1120] border-t border-gray-200 dark:border-gray-800/50">
        <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700/30">
            <div class="relative">
                <div class="w-10 h-10 bg-gradient-to-tr from-primary-500 to-accent-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-primary-500/10">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-[#0B1120] rounded-full"></div>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 dark:text-white truncate leading-tight">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-[11px] text-gray-500 dark:text-gray-500 truncate mt-0.5">
                    {{ auth()->user()->email }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
