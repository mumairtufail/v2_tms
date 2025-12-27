<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\PluginConfiguration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class PluginService
{
    public function discoverPlugins(int $companyId = null): Collection
    {
        $pluginsPath = app_path('Plugins');
        $plugins = collect();

        if (File::exists($pluginsPath)) {
            $directories = File::directories($pluginsPath);
            foreach ($directories as $directory) {
                $jsonPath = $directory . '/plugin.json';
                if (File::exists($jsonPath)) {
                    $data = json_decode(File::get($jsonPath), true);
                    if ($data) {
                        $data['directory'] = basename($directory);
                        $plugins->push($data);
                    }
                }
            }
        }

        // Get Global DB Status
        $dbPlugins = Plugin::all()->keyBy('slug');
        
        // Get Company Config Status
        $companyConfigs = $companyId 
            ? PluginConfiguration::where('company_id', $companyId)->get()->keyBy('plugin_slug') 
            : collect();

        return $plugins->map(function ($plugin) use ($dbPlugins, $companyConfigs) {
            $slug = $plugin['slug'];
            $dbPlugin = $dbPlugins->get($slug);
            $config = $companyConfigs->get($slug);

            $plugin['is_installed'] = (bool) $dbPlugin;
            $plugin['is_active_global'] = $dbPlugin ? $dbPlugin->is_active : false;
            $plugin['is_enabled_company'] = $config ? $config->is_active : false;
            $plugin['company_config_id'] = $config ? $config->id : null;
            
            return (object) $plugin;
        });
    }

    public function installPlugin(string $slug): Plugin
    {
        // Find plugin data from file
        $plugins = $this->discoverPlugins();
        $pluginData = $plugins->firstWhere('slug', $slug);

        if (!$pluginData) {
            throw new \Exception("Plugin files not found for slug: $slug");
        }

        return Plugin::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $pluginData->name,
                'description' => $pluginData->description,
                'version' => $pluginData->version,
                'author' => $pluginData->author,
                'is_installed' => true,
                'is_active' => true // Default to active globally upon install
            ]
        );
    }

    public function uninstallPlugin(string $slug): void
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();
        $plugin->delete();
        // Optionally delete configurations
        // PluginConfiguration::where('plugin_slug', $slug)->delete();
    }

    public function getCompanyPlugins(int $companyId): Collection
    {
        return PluginConfiguration::where('company_id', $companyId)->get()->keyBy('plugin_slug');
    }

    public function togglePlugin(int $companyId, string $pluginSlug, bool $isActive): PluginConfiguration
    {
        return DB::transaction(function () use ($companyId, $pluginSlug, $isActive) {
            $plugin = Plugin::where('slug', $pluginSlug)->firstOrFail();
            
            // Preserve existing configuration if updating
            $existingConfig = PluginConfiguration::where('company_id', $companyId)
                ->where('plugin_slug', $pluginSlug)
                ->first();

            return PluginConfiguration::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'plugin_slug' => $pluginSlug,
                ],
                [
                    'name' => $plugin->name,
                    'is_active' => $isActive,
                    'configuration' => $existingConfig ? $existingConfig->configuration : [],
                ]
            );
        });
    }

    public function getConfiguration(int $companyId, string $pluginSlug)
    {
        return PluginConfiguration::where('company_id', $companyId)
            ->where('plugin_slug', $pluginSlug)
            ->first();
    }

    public function updateConfiguration(int $companyId, string $pluginSlug, array $config): PluginConfiguration
    {
        return DB::transaction(function () use ($companyId, $pluginSlug, $config) {
            $pluginConfig = PluginConfiguration::where('company_id', $companyId)
                ->where('plugin_slug', $pluginSlug)
                ->first();

            if (!$pluginConfig) {
                // Create if not exists
                $plugin = Plugin::where('slug', $pluginSlug)->firstOrFail();
                $pluginConfig = PluginConfiguration::create([
                    'company_id' => $companyId,
                    'plugin_slug' => $pluginSlug,
                    'name' => $plugin->name,
                    'is_active' => true,
                    'configuration' => $config
                ]);
            } else {
                // Merge config
                $currentConfig = $pluginConfig->configuration ?? [];
                // Filter nulls
                $inputConfig = array_filter($config, function($value) {
                    return !is_null($value) && $value !== '';
                });
                $newConfig = array_merge($currentConfig, $inputConfig);
                
                $pluginConfig->update(['configuration' => $newConfig]);
            }

            return $pluginConfig;
        });
    }
}
