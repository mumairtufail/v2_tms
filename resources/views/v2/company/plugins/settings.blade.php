@extends('v2.layouts.app')

@section('title', $plugin->name . ' Settings')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Plugins', 'url' => route('v2.plugins.index', ['company' => $company->slug])],
        ['label' => $plugin->name . ' Settings']
    ]" />

    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('v2.plugins.index', ['company' => $company->slug]) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <x-page-header :title="$plugin->name . ' Settings'" description="Configure plugin settings" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Settings Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('v2.plugins.settings.update', ['company' => $company->slug, 'slug' => $plugin->slug]) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        @if(Str::contains(strtolower($plugin->slug), 'quickbooks'))
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">QuickBooks Configuration</h3>
                                
                                <x-text-input
                                    label="Client ID"
                                    name="configuration[client_id]"
                                    :value="$config ? ($config->configuration['client_id'] ?? '') : ''"
                                    required
                                />
                                
                                <x-text-input
                                    label="Client Secret"
                                    name="configuration[client_secret]"
                                    type="password"
                                    :value="$config ? ($config->configuration['client_secret'] ?? '') : ''"
                                    required
                                />
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Environment</label>
                                    <select name="configuration[base_url]" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">
                                        <option value="https://sandbox-quickbooks.api.intuit.com" {{ ($config && ($config->configuration['base_url'] ?? '') == 'https://sandbox-quickbooks.api.intuit.com') ? 'selected' : '' }}>Sandbox</option>
                                        <option value="https://quickbooks.api.intuit.com" {{ ($config && ($config->configuration['base_url'] ?? '') == 'https://quickbooks.api.intuit.com') ? 'selected' : '' }}>Production</option>
                                    </select>
                                </div>

                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Manual Token Entry (Optional)</h4>
                                    
                                    <x-text-input
                                        label="Realm ID (Company ID)"
                                        name="configuration[realm_id]"
                                        :value="$config ? ($config->configuration['realm_id'] ?? '') : ''"
                                    />
                                    
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Access Token</label>
                                        <textarea name="configuration[access_token]" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">{{ $config ? ($config->configuration['access_token'] ?? '') : '' }}</textarea>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Refresh Token</label>
                                        <textarea name="configuration[refresh_token]" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500">{{ $config ? ($config->configuration['refresh_token'] ?? '') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg">
                                No specific configuration fields defined for this plugin.
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('v2.plugins.index', ['company' => $company->slug]) }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            @if(Str::contains(strtolower($plugin->slug), 'quickbooks'))
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Connection Status</h3>
                    
                    @if($config && !empty($config->configuration['access_token']))
                        <div class="flex items-center gap-2 text-green-600 dark:text-green-400 mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="font-medium">Connected</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Realm ID: {{ $config->configuration['realm_id'] ?? 'N/A' }}
                        </p>
                    @else
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-medium">Not Connected</span>
                        </div>
                    @endif

                    <a href="{{ route('v2.plugins.quickbooks.connect', ['company' => $company->slug]) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        {{ ($config && !empty($config->configuration['access_token'])) ? 'Reconnect QuickBooks' : 'Connect QuickBooks' }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
