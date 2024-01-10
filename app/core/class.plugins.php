<?php

namespace leantime\core;

use leantime\core\environment;
use leantime\core\eventhelpers;

/**
 * Plugins class
 *
 * @package    leantime
 * @subpackage core
 */
class plugins
{
    use eventhelpers;

    /**
     * Enabled plugins
     *
     * @var    array
     */
    private $enabledPlugins = [];

    /**
     * constructor
     *
     * @param \leantime\core\environment $config
     * @return self
     */
    public function __construct(\leantime\core\environment $config)
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
     *
     * @access private
     *
     * @param array $plugins
     *
     * @return array
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
     *
     * @access public
     *
     * @return array
     */
    public function getEnabledPlugins(): array
    {
        return $this->enabledPlugins;
    }

    /**
     * Checks to see if a plugin is enabled
     *
     * @access public
     *
     * @param string $plugin_name
     *
     * @return boolean
     */
    public function isPluginEnabled(string $plugin_name): bool
    {
        $plugin_name = strtolower($plugin_name);

        if (
            in_array($plugin_name, array_keys($this->enabledPlugins)) && $this->enabledPlugins[$plugin_name] == true
        ) {
            return true;
        }

        return false;
    }
}