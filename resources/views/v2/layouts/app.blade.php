<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarMobileOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TMS') }} - @yield('title', 'Dashboard')</title>

    @php
        $faviconCompany = app()->bound('current.company') ? app('current.company') : null;
        $faviconLogo = $faviconCompany?->logo_light ?? \App\Models\SystemSetting::instance()->logo_light ?? null;
        $faviconUrl = \App\Support\BrandingHelper::getUrl($faviconLogo);
        $impersonating = session()->has('impersonating_original_id');
    @endphp
    @if($faviconUrl)
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Critical CSS for Alpine.js x-cloak (must load before JS) -->
    <style>[x-cloak] { display: none !important; }</style>

    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    {{-- Global Toast Notifications - Using Session Flash Messages --}}
    <x-toast-notifications />

    {{-- Impersonation Banner --}}
    @if($impersonating)
    <div class="fixed top-0 left-0 right-0 z-[80] h-10 bg-primary-700 text-white text-xs flex items-center justify-between px-5 gap-4 shadow-md">
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-3.5 h-3.5 flex-shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="truncate">
                Impersonating <strong class="font-semibold">{{ auth()->user()->name }}</strong>
                <span class="opacity-60 mx-1">&mdash;</span>
                <span class="opacity-80">{{ auth()->user()->company->name ?? 'No Company' }}</span>
            </span>
        </div>
        <form action="{{ route('admin.impersonate.stop') }}" method="POST" class="flex-shrink-0">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 px-3 py-1 bg-white/20 hover:bg-white/30 text-white font-semibold rounded transition-colors whitespace-nowrap border border-white/30">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Stop &amp; Return to Admin
            </button>
        </form>
    </div>
    @endif

    <div class="min-h-screen flex {{ $impersonating ? 'pt-10' : '' }}">

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
