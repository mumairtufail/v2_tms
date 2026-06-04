<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\PluginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PluginController extends Controller
{
    protected $pluginService;

    public function __construct(PluginService $pluginService)
    {
        $this->pluginService = $pluginService;
    }

    public function index(Company $company)
    {
        // Use discoverPlugins to get full list including file-system plugins
        $plugins = $this->pluginService->discoverPlugins($company->id);

        return view('v2.company.plugins.index', compact('company', 'plugins'));
    }

    public function install(Request $request, Company $company, string $slug)
    {
        try {
            $this->pluginService->installPlugin($slug);
            return back()->with('success', 'Plugin installed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uninstall(Request $request, Company $company, string $slug)
    {
        try {
            $this->pluginService->uninstallPlugin($slug);
            return back()->with('success', 'Plugin uninstalled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggle(Request $request, Company $company)
    {
        $request->validate([
            'plugin_slug' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $this->pluginService->togglePlugin(
            $company->id,
            $request->plugin_slug,
            $request->is_active
        );

        return back()->with('success', 'Plugin status updated successfully.');
    }

    public function settings(Company $company, string $slug)
    {
        $config = $this->pluginService->getConfiguration($company->id, $slug);
        
        // We need plugin info too
        $plugins = $this->pluginService->discoverPlugins($company->id);
        $plugin = $plugins->firstWhere('slug', $slug);

        if (!$plugin) {
            return back()->with('error', 'Plugin not found.');
        }

        return view('v2.company.plugins.settings', compact('company', 'plugin', 'config'));
    }

    public function updateSettings(Request $request, Company $company, string $slug)
    {
        $request->validate([
            'configuration' => 'required|array',
        ]);

        $this->pluginService->updateConfiguration(
            $company->id,
            $slug,
            $request->configuration
        );

        return back()->with('success', 'Settings saved successfully.');
    }

    // QuickBooks OAuth
    public function connectQuickBooks(Request $request, Company $company)
    {
        $config = $this->pluginService->getConfiguration($company->id, 'quickbooks');
        
        if (!$config) {
            return back()->with('error', 'Please save QuickBooks settings first.');
        }

        $clientId = $config->configuration['client_id'] ?? null;
        if (!$clientId) {
             return back()->with('error', 'Client ID is missing.');
        }

        $scope = 'com.intuit.quickbooks.accounting';
        $redirectUri = route('v2.plugins.quickbooks.callback', ['company' => $company->slug]);
        $state = $company->id . '_' . csrf_token(); 

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

    public function callbackQuickBooks(Request $request, Company $company)
    {
        $code = $request->query('code');
        $state = $request->query('state');
        $realmId = $request->query('realmId');

        if (!$code || !$state) {
            return redirect()->route('v2.plugins.settings', ['company' => $company->slug, 'slug' => 'quickbooks'])->with('error', 'Invalid OAuth response.');
        }

        // Verify state (company id check)
        $parts = explode('_', $state);
        if ($parts[0] != $company->id) {
             return redirect()->route('v2.plugins.settings', ['company' => $company->slug, 'slug' => 'quickbooks'])->with('error', 'Invalid state.');
        }

        $config = $this->pluginService->getConfiguration($company->id, 'quickbooks');
        $clientId = $config->configuration['client_id'];
        $clientSecret = $config->configuration['client_secret'];
        $redirectUri = route('v2.plugins.quickbooks.callback', ['company' => $company->slug]);

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
            
            $newConfig = $config->configuration;
            $newConfig['access_token'] = $tokens['access_token'];
            $newConfig['refresh_token'] = $tokens['refresh_token'];
            $newConfig['realm_id'] = $realmId;
            
            $this->pluginService->updateConfiguration($company->id, 'quickbooks', $newConfig);

            return redirect()->route('v2.plugins.settings', ['company' => $company->slug, 'slug' => 'quickbooks'])->with('success', 'Connected to QuickBooks successfully!');
        } else {
            return redirect()->route('v2.plugins.settings', ['company' => $company->slug, 'slug' => 'quickbooks'])->with('error', 'Failed to connect: ' . $response->body());
        }
    }
}
