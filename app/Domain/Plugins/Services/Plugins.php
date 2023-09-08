<?php

namespace Leantime\Domain\Plugins\Services {

    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
    use Leantime\Domain\Plugins\Models\Plugins as PluginModel;
    use Ramsey\Uuid\Uuid;

    class Plugins
    {
        private PluginRepository $pluginRepository;
        private string $pluginDirectory =  ROOT . "/../app/plugins/";
        private EnvironmentCore $config;


        public function __construct(PluginRepository $pluginRepository, EnvironmentCore $config)
        {
            $this->pluginRepository = $pluginRepository;
            $this->config = $config;
        }

        public function getAllPlugins()
        {
            return $this->pluginRepository->getAllPlugins(false);
        }

        public function isPluginEnabled($pluginFolder)
        {

            $plugins = $this->getEnabledPlugins();

            foreach ($plugins as $plugin) {
                if (strtolower($plugin->foldername) == strtolower($pluginFolder)) {
                    return true;
                }
            }

            return false;
        }

        public function getEnabledPlugins()
        {
            if (isset($_SESSION['enabledPlugins'])) {
                return $_SESSION['enabledPlugins'];
            }

            $_SESSION['enabledPlugins'] = $this->pluginRepository->getAllPlugins(true);

            // Gets plugins from the config, which are automatically enabled
            if (
                isset($this->config->plugins)
                && $configplugins = explode(',', $this->config->plugins)
            ) {
                $configplugins = array_map(function ($pluginStr) {
                    $pluginModel = app()->make(\Leantime\Domain\Plugins\Models\Plugins::class);
                    $pluginModel->foldername = $pluginStr;
                    $pluginModel->name = $pluginStr;
                    $pluginModel->enabled = true;

                    return $pluginModel;
                }, $configplugins);

                $_SESSION['enabledPlugins'] = array_merge($_SESSION['enabledPlugins'], $configplugins);
            }

            return $_SESSION['enabledPlugins'];
        }

        public function discoverNewPlugins()
        {

            $installedPlugins = $this->getAllPlugins();
            //Simplify list of installed plugin for a quicker array_Search
            $installedPluginNames = array();

            foreach ($installedPlugins as $plugin) {
                $installedPluginNames[] = $plugin->foldername;
            }

            $scanned_directory = array_diff(scandir($this->pluginDirectory), array('..', '.'));

            $newPlugins = [];

            foreach ($scanned_directory as $directory) {
                if (is_dir($this->pluginDirectory . "/" . $directory) && array_search($directory, $installedPluginNames) === false) {
                    $pluginJsonFile = $this->pluginDirectory . "/" . $directory . "/composer.json";

                    if (is_file($pluginJsonFile)) {
                        $json = file_get_contents($pluginJsonFile);

                        $pluginFile = json_decode($json, true);
                        $plugin = app()->make(\Leantime\Domain\Plugins\Models\Plugins::class);
                        $plugin->name = $pluginFile['name'];
                        $plugin->enabled = 0;
                        $plugin->description = $pluginFile['description'];
                        $plugin->version = $pluginFile['version'];
                        $plugin->installdate = '';
                        $plugin->foldername = $directory;
                        $plugin->homepage = $pluginFile['homepage'];
                        $plugin->authors = $pluginFile['authors'];

                        $newPlugins[] = $plugin;
                    }
                }
            }

            return $newPlugins;
        }

        public function installPlugin($pluginFolder)
        {

            $pluginFolder = strip_tags(stripslashes($pluginFolder));
            $pluginJsonFile = $this->pluginDirectory . "/" . $pluginFolder . "/composer.json";

            if (is_file($pluginJsonFile)) {
                $json = file_get_contents($pluginJsonFile);

                $pluginFile = json_decode($json, true);
                $plugin = app()->make(PluginModel::class);
                $plugin->name = $pluginFile['name'];
                $plugin->enabled = 0;
                $plugin->description = $pluginFile['description'];
                $plugin->version = $pluginFile['version'];
                $plugin->installdate = date("Y-m-d");
                $plugin->foldername = $pluginFolder;
                $plugin->homepage = $pluginFile['homepage'];
                $plugin->authors = json_encode($pluginFile['authors']);

                //Any installation calls should happen right here.
                $pluginClassName = $this->getPluginClassName($plugin);
                $newPluginSvc = app()->make($pluginClassName);

                if (method_exists($newPluginSvc, "install")) {
                    try {
                        $newPluginSvc->install();
                    } catch (\Exception $e) {
                        error_log($e);
                        return false;
                    }
                }

                return $this->pluginRepository->addPlugin($plugin);
            }

            return false;
        }

        public function enablePlugin(int $id)
        {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->enablePlugin($id);
        }

        public function disablePlugin(int $id)
        {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->disablePlugin($id);
        }

        public function removePlugin(int $id)
        {
            unset($_SESSION['enabledPlugins']);
            /** @var PluginModel|false */
            $plugin = $this->pluginRepository->getPlugin($id);

            if (! $plugin) {
                return false;
            }

            //Any installation calls should happen right here.
            $pluginClassName = $this->getPluginClassName($plugin);
            $newPluginSvc = app()->make($pluginClassName);

            if (method_exists($newPluginSvc, "uninstall")) {
                try {
                    $newPluginSvc->install();
                } catch (\Exception $e) {
                    error_log($e);
                    return false;
                }
            }

            return $this->pluginRepository->removePlugin($id);

            //TODO remove files savely
        }

        public function getPluginClassName(PluginModel $plugin)
        {
            return app()->getNamespace()
                . 'Plugins\\'
                . htmlspecialchars($plugin->foldername)
                . '\\Services\\'
                . htmlspecialchars($plugin->foldername);
        }
    }
}
