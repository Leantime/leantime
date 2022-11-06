<?php

namespace leantime\core;

use leantime\core\config;

class plugins {
    /**
     * Enabled plugins
     * @access private
     * @var    array
     */
    private $enabledPlugins = [];

    public function __construct()
    {
        $this->enabledPlugins = array_map(
            function ($plugin_key) {
                return strtolower($plugin_key);
            },
            (array) (new config)->getEnabledPlugins()
        );
    }

    public function getEnabledPlugins()
    {
        return $this->enabledPlugins;
    }

    public function isPluginEnabled($plugin_name)
    {
        $plugin_name = strtolower($plugin_name);

        if (in_array($plugin_name, $this->enabledPlugins)) {
            return true;
        }

        return false;
    }
}
