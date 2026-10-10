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
     * When the enabled plugins can't be read (no database yet, e.g. during install or an outage) this
     * falls back to the system plugins from config. Pass $strict to get the exception instead — callers
     * that cache the result must not cache that fallback.
     *
     * @param  bool  $strict  Rethrow a failed plugin lookup instead of falling back to the system plugins
     * @return array Array of plugin paths with format information
     *
     * @throws \Exception When $strict and the enabled plugins can't be read
     */
    public function getEnabledPluginPaths(bool $strict = false): array
    {
        $pluginPaths = [];
        $pluginDirectory = APP_ROOT.'/app/Plugins/';

        // Get enabled plugins from the domain service
        try {
            $pluginService = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class);
            // In strict mode the domain service rethrows a failed plugin query instead of returning its own
            // system-plugin fallback, so the caller can tell a real empty list from an unreadable one.
            $enabledPlugins = $pluginService->getEnabledPlugins(failOnDatabaseError: $strict);

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
            if ($strict) {
                throw $e;
            }

            // Fall back to system plugins if service unavailable
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

        return $pluginPaths;
    }
}
