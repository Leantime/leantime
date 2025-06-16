<?php

namespace Leantime\Core\Plugins;

use Leantime\Core\Configuration;
use Leantime\Core\Events\DispatchesEvents;

/**
 * Plugins class
 */
class Plugins
{
    use DispatchesEvents;

    /**
     * Enabled plugins
     */
    private array $enabledPlugins = [];

    /**
     * constructor
     *
     * @return void
     */
    public function __construct(Configuration\Environment $config)
    {
        if (isset($config->plugins)) {
            $plugins = json_decode($config->plugins);
        } else {
            $plugins = [];
        }

        $this->enabledPlugins = $this->standardize_plugin_keys(
            (array) $plugins
        );
    }

    /**
     * Makes all plugin keys lowercase for easy comparisons
     */
    private function standardize_plugin_keys(array $plugins): array
    {
        foreach ($plugins as $plugin_key => $plugin_enabled) {
            if ($plugin_key == strtolower($plugin_key)) {
                continue;
            }

            $plugins[strtolower($plugin_key)] = $plugin_enabled;
            unset($plugins[$plugin_key]);
        }

        return $plugins;
    }

    /**
     * Gets all plugin enabled/disabled settings
     */
    public function getEnabledPlugins(): array
    {
        return $this->enabledPlugins;
    }

    /**
     * Checks to see if a plugin is enabled
     */
    public function isPluginEnabled(string $plugin_name): bool
    {
        $plugin_name = strtolower($plugin_name);

        if (
            in_array($plugin_name, array_keys($this->enabledPlugins)) && $this->enabledPlugins[$plugin_name]
        ) {
            return true;
        }

        return false;
    }

    /**
     * Gets paths for enabled plugins, supporting both folder and phar formats
     *
     * @return array Array of plugin paths with format information
     */
    public function getEnabledPluginPaths(): array
    {
        $pluginPaths = [];
        $pluginDirectory = APP_ROOT.'/app/Plugins/';

        // Get enabled plugins from the domain service
        try {
            $pluginService = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class);
            $enabledPlugins = $pluginService->getEnabledPlugins();

            foreach ($enabledPlugins as $plugin) {
                // Skip incomplete class objects
                if (is_a($plugin, '__PHP_Incomplete_Class') || $plugin == null) {
                    continue;
                }

                $pluginPath = $pluginDirectory.$plugin->foldername;

                if ($plugin->format == 'phar') {
                    $pharPath = "phar://{$pluginPath}/{$plugin->foldername}.phar";

                    if (file_exists($pharPath)) {
                        $pluginPaths[] = [
                            'path' => $pharPath,
                            'foldername' => $plugin->foldername,
                            'format' => 'phar',
                            'namespace' => "Leantime\\Plugins\\{$plugin->foldername}\\",
                        ];
                    }
                } else {
                    // Folder-based plugin
                    if (is_dir($pluginPath)) {
                        $pluginPaths[] = [
                            'path' => $pluginPath,
                            'foldername' => $plugin->foldername,
                            'format' => 'folder',
                            'namespace' => "Leantime\\Plugins\\{$plugin->foldername}\\",
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Fall back to system plugins if service unavailable
            if (isset($this->enabledPlugins)) {
                foreach ($this->enabledPlugins as $pluginName => $enabled) {
                    if ($enabled) {
                        $pluginPath = $pluginDirectory.$pluginName;
                        if (is_dir($pluginPath)) {
                            $pluginPaths[] = [
                                'path' => $pluginPath,
                                'foldername' => $pluginName,
                                'format' => 'folder',
                                'namespace' => "Leantime\\Plugins\\{$pluginName}\\",
                            ];
                        }
                    }
                }
            }
        }

        return $pluginPaths;
    }
}
