<!-- Top Navbar -->
@php
    $currentCompany = app()->bound('current.company') ? app('current.company') : null;
@endphp
<nav 
    class="navbar fixed left-0 right-0 lg:left-64 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 transition-all duration-300 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 z-30 {{ ($impersonating ?? false) ? 'top-10' : 'top-0' }}"
>
    <!-- Left Side -->
    <div class="flex items-center gap-4">
        <!-- Mobile Menu Button -->
        <button 
            @click="sidebarMobileOpen = true"
            class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Breadcrumb / Page Title -->
        <div class="hidden sm:block">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white tracking-tight">
                <!-- @yield('title', 'Dashboard') -->
            </h1>
        </div>
    </div>

    <!-- Right Side -->
    <div class="flex items-center gap-3">
        <!-- Search Button -->
        <button class="flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>

        @if($currentCompany)
        <!-- Customer Portal Login -->
        <a
            href="{{ route('portal.login', ['company' => $currentCompany->slug]) }}"
            target="_blank"
            rel="noopener"
            title="Customer Portal Login"
            class="relative flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14a7 7 0 00-7 7h9"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18h6m0 0l-2.5-2.5M21 18l-2.5 2.5"/>
            </svg>
        </a>
        @endif

        <!-- Dark Mode Toggle -->
        <button 
            @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
            class="relative flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-all duration-300"
            :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
            <!-- Sun Icon (shown in dark mode) -->
            <svg 
                x-show="darkMode" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 rotate-90 scale-0"
                x-transition:enter-end="opacity-100 rotate-0 scale-100"
                class="w-5 h-5 text-amber-400" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <!-- Moon Icon (shown in light mode) -->
            <svg 
                x-show="!darkMode" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -rotate-90 scale-0"
                x-transition:enter-end="opacity-100 rotate-0 scale-100"
                class="w-5 h-5 text-blue-500" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>


        <!-- Notifications -->
        @if($currentCompany)
        <x-notification-bell
            :index-url="route('v2.notifications.index', ['company' => $currentCompany->slug])"
            :read-all-url="route('v2.notifications.read-all', ['company' => $currentCompany->slug])"
            :read-url-template="route('v2.notifications.read', ['company' => $currentCompany->slug, 'notification' => '__ID__'])"
        />
        @endif

        <!-- Divider -->
        <div class="hidden sm:block w-px h-8 bg-gray-200 dark:bg-gray-700"></div>

        <!-- Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = !open"
                class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-accent-600 rounded-xl flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                {{-- Name + role only; email and company live in the dropdown so nothing repeats --}}
                <div class="hidden md:block text-left min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-white truncate max-w-[10rem]">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[10rem]">{{ auth()->user()->primaryRoleLabel() }}</p>
                </div>
                <svg class="hidden md:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Profile Dropdown Menu -->
            <div 
                x-show="open" 
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-2 z-50"
                style="display: none;"
            >
                <!-- Account -->
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 space-y-3">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Signed in as</p>
                        <p class="mt-0.5 text-sm text-gray-900 dark:text-white truncate" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p>
                    </div>
                    @if($currentCompany)
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Company</p>
                            <div class="mt-0.5 flex items-center gap-2">
                                <p class="min-w-0 flex-1 text-sm text-gray-900 dark:text-white truncate" title="{{ $currentCompany->name }}">{{ $currentCompany->name }}</p>
                                @if($currentCompany->shortcode)
                                    <span class="shrink-0 rounded bg-primary-50 dark:bg-primary-900/30 px-1.5 py-0.5 text-[10px] font-semibold text-primary-700 dark:text-primary-300">{{ $currentCompany->shortcode }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Menu Items -->
                <div class="py-2">
                    <a href="{{ $currentCompany ? route('v2.profile.edit', ['company' => $currentCompany->slug]) : route('admin.profile') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>My Profile</span>
                    </a>
                </div>
                
                <!-- Logout -->
                <div class="border-t border-gray-100 dark:border-gray-700 py-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-left">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Sign out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
