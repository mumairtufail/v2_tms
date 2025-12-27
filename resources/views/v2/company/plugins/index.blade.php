@extends('v2.layouts.app')

@section('title', 'Plugins')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Plugins']]" />

    <!-- Header -->
    <x-page-header title="Plugins" description="Manage your organization's plugins" />

    <!-- Plugins Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($plugins as $plugin)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col h-full">
                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700/50 rounded-xl flex items-center justify-center">
                            @if(Str::contains(strtolower($plugin->slug), 'quickbooks'))
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            @elseif(Str::contains(strtolower($plugin->slug), 'stripe'))
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                            @else
                                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                            @endif
                        </div>
                        
                        @if($plugin->is_enabled_company)
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.outside="open = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                                <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-10" style="display: none;">
                                    <a href="{{ route('v2.plugins.settings', ['company' => $company->slug, 'slug' => $plugin->slug]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        Settings
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plugin->name }}</h3>
                            @if($plugin->is_enabled_company)
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900/30 rounded-full">Active</span>
                            @elseif($plugin->is_installed)
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-700 rounded-full">Installed</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">by {{ $plugin->author }} • v{{ $plugin->version }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $plugin->description }}</p>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700/50 space-y-2">
                        @if(!$plugin->is_installed)
                            <form action="{{ route('v2.plugins.install', ['company' => $company->slug, 'slug' => $plugin->slug]) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Install Plugin
                                </button>
                            </form>
                        @else
                            @if($plugin->is_enabled_company)
                                <form action="{{ route('v2.plugins.toggle', ['company' => $company->slug]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plugin_slug" value="{{ $plugin->slug }}">
                                    <input type="hidden" name="is_active" value="0">
                                    <button type="submit" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('v2.plugins.toggle', ['company' => $company->slug]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plugin_slug" value="{{ $plugin->slug }}">
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-900/50 hover:bg-green-50 dark:hover:bg-green-900/20 text-green-700 dark:text-green-400 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Activate
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('v2.plugins.uninstall', ['company' => $company->slug, 'slug' => $plugin->slug]) }}" method="POST" onsubmit="return confirm('Are you sure? This will remove the plugin globally.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Uninstall
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No plugins found</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Check your app/Plugins directory.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
