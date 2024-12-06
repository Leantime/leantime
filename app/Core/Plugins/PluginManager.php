<?php

namespace Leantime\Core\Plugins;

class PluginManager
{
    public function __construct() {}

    public function loadPlugin(string $pluginPath): void
    {
        $registerFile = $pluginPath.'/register.php';
        if (file_exists($registerFile)) {
            require_once $registerFile;
        }
    }
}
