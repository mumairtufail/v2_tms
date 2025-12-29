<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarMobileOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TMS') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Critical CSS for Alpine.js x-cloak (must load before JS) -->
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    {{-- Global Toast Notifications --}}
    <x-ui.toast position="bottom-right" maxToasts="5" />
    
    <div class="min-h-screen flex">
        
        <!-- Sidebar (Fixed) -->
        @include('v2.partials.sidebar')
        
        <!-- Mobile Sidebar Overlay -->
        <div 
            x-show="sidebarMobileOpen" 
            x-transition.opacity
            @click="sidebarMobileOpen = false"
            class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-40 lg:hidden"
            x-cloak
        ></div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-64 transition-all duration-200 overflow-x-hidden">
            <!-- Top Navbar -->
            @include('v2.partials.navbar')

            <!-- Page Content -->
            <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 pt-24">
                <div class="max-w-7xl mx-auto w-full">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
