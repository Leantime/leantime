<?php

namespace Leantime\Domain\Plugins\Services {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
    use Leantime\Domain\Plugins\Models\Plugins as PluginModel;
    use Ramsey\Uuid\Uuid;

    /**
     *
     */
    class Plugins
    {
        private PluginRepository $pluginRepository;
        private string $pluginDirectory =  ROOT . "/../app/Plugins/";
        private EnvironmentCore $config;


        /**
         * @param PluginRepository $pluginRepository
         * @param EnvironmentCore  $config
         */
        public function __construct(PluginRepository $pluginRepository, EnvironmentCore $config)
        {
            $this->pluginRepository = $pluginRepository;
            $this->config = $config;
        }

        /**
         * @return array|false
         */
        /**
         * @return array|false
         */
        public function getAllPlugins(): false|array
        {
            return $this->pluginRepository->getAllPlugins(false);
        }

        /**
         * @param $pluginFolder
         * @return bool
         * @throws BindingResolutionException
         */
        /**
         * @param $pluginFolder
         * @return boolean
         * @throws BindingResolutionException
         */
        public function isPluginEnabled($pluginFolder): bool
        {

            $plugins = $this->getEnabledPlugins();

            foreach ($plugins as $plugin) {
                if (strtolower($plugin->foldername) == strtolower($pluginFolder)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @return array|false|mixed
         * @throws BindingResolutionException
         */
        /**
         * @return array|false|mixed
         * @throws BindingResolutionException
         */
        public function getEnabledPlugins(): mixed
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
                    $pluginModel = app()->make(PluginModel::class);
                    $pluginModel->foldername = $pluginStr;
                    $pluginModel->name = $pluginStr;
                    $pluginModel->enabled = true;

                    return $pluginModel;
                }, $configplugins);

                $_SESSION['enabledPlugins'] = array_merge($_SESSION['enabledPlugins'], $configplugins);
            }

            return $_SESSION['enabledPlugins'];
        }

        /**
         * @return array
         * @throws BindingResolutionException
         */
        /**
         * @return array
         * @throws BindingResolutionException
         */
        public function discoverNewPlugins(): array
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
                        $plugin = app()->make(PluginModel::class);
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

        /**
         * @param $pluginFolder
         * @return false|string
         * @throws BindingResolutionException
         */
        /**
         * @param $pluginFolder
         * @return false|string
         * @throws BindingResolutionException
         */
        public function installPlugin($pluginFolder): false|string
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
                    } catch (Exception $e) {
                        error_log($e);
                        return false;
                    }
                }

                return $this->pluginRepository->addPlugin($plugin);
            }

            return false;
        }

        /**
         * @param int $id
         * @return bool
         */
        /**
         * @param integer $id
         * @return boolean
         */
        public function enablePlugin(int $id): bool
        {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->enablePlugin($id);
        }

        /**
         * @param int $id
         * @return bool
         */
        /**
         * @param integer $id
         * @return boolean
         */
        public function disablePlugin(int $id): bool
        {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->disablePlugin($id);
        }

        /**
         * @param int $id
         * @return bool
         * @throws BindingResolutionException
         */
        /**
         * @param integer $id
         * @return boolean
         * @throws BindingResolutionException
         */
        public function removePlugin(int $id): bool
        {
            unset($_SESSION['enabledPlugins']);
            /** @var PluginModel|false $plugin */
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
                } catch (Exception $e) {
                    error_log($e);
                    return false;
                }
            }

            return $this->pluginRepository->removePlugin($id);

            //TODO remove files savely
        }

        /**
         * @param PluginModel $plugin
         * @return string
         * @throws BindingResolutionException
         */
        /**
         * @param PluginModel $plugin
         * @return string
         * @throws BindingResolutionException
         */
        public function getPluginClassName(PluginModel $plugin): string
        {
            return app()->getNamespace()
                . 'Plugins\\'
                . htmlspecialchars(ucfirst($plugin->foldername))
                . '\\Services\\'
                . htmlspecialchars(ucfirst($plugin->foldername));
        }
    }
}
