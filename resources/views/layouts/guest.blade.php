<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-950 relative overflow-hidden">
        <!-- Background Gradients -->
        <div class="fixed inset-0 -z-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[1000px] bg-primary-400/20 rounded-full blur-3xl opacity-30 mix-blend-multiply dark:mix-blend-screen animate-blob"></div>
            <div class="absolute bottom-0 right-0 w-[800px] h-[800px] bg-accent-400/20 rounded-full blur-3xl opacity-30 mix-blend-multiply dark:mix-blend-screen animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-0 w-[800px] h-[800px] bg-purple-400/20 rounded-full blur-3xl opacity-30 mix-blend-multiply dark:mix-blend-screen animate-blob animation-delay-4000"></div>
            <!-- Grid Pattern -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjAwLCAyMDAsIDIwMCwgMC4yKSIvPjwvc3ZnPg==')] dark:bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LCAyNTUsIDI1NSwgMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
        </div>

        <!-- Theme Toggle -->
        <div class="fixed top-6 right-6 z-50">
            <button 
                @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-200/50 dark:border-gray-700/50 text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-300 hover:scale-105"
            >
                <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- <div class="mb-8 transform hover:scale-105 transition-transform duration-300">
                <a href="/">
                    <x-application-logo class="w-auto h-16" />
                </a>
            </div> -->

            <div class="w-full sm:max-w-md px-8 py-10 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl shadow-2xl shadow-gray-200/50 dark:shadow-black/50 border border-white/20 dark:border-gray-700/50 overflow-hidden sm:rounded-3xl relative group">
                <!-- Card Glow Effect -->
                <div class="absolute -inset-1 bg-gradient-to-r from-primary-500/20 to-accent-500/20 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative">
                    {{ $slot }}
                </div>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </body>
</html>
