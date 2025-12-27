<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class PluginController extends Controller
{
    public function index()
    {
        $pluginsPath = app_path('Plugins');
        $availablePlugins = [];

        // Ensure Plugins directory exists
        if (File::exists($pluginsPath)) {
            $directories = File::directories($pluginsPath);

            foreach ($directories as $directory) {
                $jsonPath = $directory . '/plugin.json';
                if (File::exists($jsonPath)) {
                    $pluginData = json_decode(File::get($jsonPath), true);
                    if ($pluginData) {
                        // Check if plugin exists in DB
                        $dbPlugin = Plugin::where('slug', $pluginData['slug'])->first();
                        
                        $pluginData['is_installed'] = $dbPlugin ? $dbPlugin->is_installed : false;
                        $pluginData['is_active'] = $dbPlugin ? $dbPlugin->is_active : false;
                        $pluginData['db_id'] = $dbPlugin ? $dbPlugin->id : null;
                        
                        $availablePlugins[] = $pluginData;
                    }
                }
            }
        }

        return view('dashboard.plugins.index', compact('availablePlugins'));
    }

    public function activate($slug)
    {
        $pluginPath = app_path("Plugins/" . $this->getDirectoryName($slug) . "/plugin.json");
        
        if (!File::exists($pluginPath)) {
            return redirect()->back()->with('error', 'Plugin files not found.');
        }

        $pluginData = json_decode(File::get($pluginPath), true);
        
        $plugin = Plugin::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $pluginData['name'],
                'description' => $pluginData['description'],
                'version' => $pluginData['version'],
                'author' => $pluginData['author'],
                'is_installed' => true,
                'is_active' => true
            ]
        );

        return redirect()->back()->with('success', 'Plugin activated successfully.');
    }

    public function deactivate($slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();
        
        if ($plugin) {
            $plugin->update(['is_active' => false]);
            return redirect()->back()->with('success', 'Plugin deactivated successfully.');
        }

        return redirect()->back()->with('error', 'Plugin not found.');
    }

    public function uninstall($slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();
        
        if ($plugin) {
            $plugin->delete();
            return redirect()->back()->with('success', 'Plugin uninstalled successfully.');
        }

        return redirect()->back()->with('error', 'Plugin not found.');
    }

    // Configuration Methods

    public function settings($slug)
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();
        
        // Get configurations for the current company
        // Assuming auth()->user()->company_id exists, otherwise handle appropriately
        $companyId = auth()->user()->company_id; 
        
        $configurations = \App\Models\PluginConfiguration::where('plugin_slug', $slug)
            ->where('company_id', $companyId)
            ->get();

        return view('dashboard.plugins.settings', compact('plugin', 'configurations'));
    }

    public function storeSettings(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'configuration' => 'required|array',
        ]);

        $companyId = auth()->user()->company_id;

        Log::channel('plugins')->info("Saving new configuration for $slug", ['data' => $request->configuration]);

        \App\Models\PluginConfiguration::create([
            'company_id' => $companyId,
            'plugin_slug' => $slug,
            'name' => $request->name,
            'configuration' => $request->configuration,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('plugins.settings', $slug)->with('success', 'Configuration saved successfully.');
    }

    public function updateSettings(Request $request, $slug, $id)
    {
        $config = \App\Models\PluginConfiguration::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'configuration' => 'required|array',
        ]);

        Log::channel('plugins')->info("Updating configuration $id for $slug", ['new_data' => $request->configuration]);

        // Merge with existing config to prevent losing keys not in the form
        $currentConfig = $config->configuration ?? [];
        
        // Filter out null or empty values from request to prevent overwriting existing data
        $inputConfig = array_filter($request->configuration, function($value) {
            return !is_null($value) && $value !== '';
        });

        $newConfig = array_merge($currentConfig, $inputConfig);

        $config->update([
            'name' => $request->name,
            'configuration' => $newConfig,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('plugins.settings', $slug)->with('success', 'Configuration updated successfully.');
    }

    public function destroySettings($slug, $id)
    {
        $config = \App\Models\PluginConfiguration::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $config->delete();

        return redirect()->route('plugins.settings', $slug)->with('success', 'Configuration deleted successfully.');
    }

    // QuickBooks OAuth Methods

    public function connectQuickBooks(Request $request)
    {
        $configId = $request->query('config_id');
        $config = \App\Models\PluginConfiguration::where('id', $configId)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $clientId = $config->configuration['client_id'] ?? null;
        $baseUrl = $config->configuration['base_url'] ?? 'https://sandbox-quickbooks.api.intuit.com';
        
        // Determine scope and redirect URI
        $scope = 'com.intuit.quickbooks.accounting';
        $redirectUri = route('plugins.quickbooks.callback');
        $state = $config->id . '_' . csrf_token(); // Pass config ID in state

        // Construct Auth URL
        // Discovery doc: https://developer.intuit.com/app/developer/qbo/docs/develop/authentication-and-authorization/oauth-2.0
        $authUrl = "https://appcenter.intuit.com/connect/oauth2";
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => $scope,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ];

        return redirect($authUrl . '?' . http_build_query($params));
    }

    public function callbackQuickBooks(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');
        $realmId = $request->query('realmId');

        if (!$code || !$state) {
            return redirect()->route('plugins.index')->with('error', 'Invalid OAuth response.');
        }

        // Extract config ID from state
        $parts = explode('_', $state);
        $configId = $parts[0];

        $config = \App\Models\PluginConfiguration::where('id', $configId)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $clientId = $config->configuration['client_id'];
        $clientSecret = $config->configuration['client_secret'];
        $redirectUri = route('plugins.quickbooks.callback');

        // Exchange code for tokens
        $tokenUrl = "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer";
        $authHeader = base64_encode("$clientId:$clientSecret");

        $response = Http::asForm()->withHeaders([
            'Authorization' => "Basic $authHeader",
            'Accept' => 'application/json',
        ])->post($tokenUrl, [
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if ($response->successful()) {
            $tokens = $response->json();
            
            // Update configuration with new tokens
            $newConfig = $config->configuration;
            $newConfig['access_token'] = $tokens['access_token'];
            $newConfig['refresh_token'] = $tokens['refresh_token'];
            $newConfig['realm_id'] = $realmId; // Save Realm ID from callback
            
            // Calculate expiry if needed, but for now just save tokens
            // $newConfig['expires_in'] = $tokens['expires_in'];
            
            $config->update(['configuration' => $newConfig]);

            return redirect()->route('plugins.settings', 'quickbooks')->with('success', 'Connected to QuickBooks successfully!');
        } else {
            return redirect()->route('plugins.settings', 'quickbooks')->with('error', 'Failed to connect: ' . $response->body());
        }
    }

    // Helper to find directory name from slug (assuming simple mapping for now, or scan)
    private function getDirectoryName($slug)
    {
        // For this simple implementation, we assume directory name matches PascalCase of slug or we scan
        // A better way is to scan all plugin.json files again to find the matching slug
        $pluginsPath = app_path('Plugins');
        if (File::exists($pluginsPath)) {
            $directories = File::directories($pluginsPath);
            foreach ($directories as $directory) {
                $jsonPath = $directory . '/plugin.json';
                if (File::exists($jsonPath)) {
                    $data = json_decode(File::get($jsonPath), true);
                    if (($data['slug'] ?? '') === $slug) {
                        return basename($directory);
                    }
                }
            }
        }
        return $slug; // Fallback
    }
}
