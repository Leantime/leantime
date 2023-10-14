<?php

namespace Leantime\Domain\Plugins\Services {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
    use Leantime\Domain\Plugins\Models\Plugins as PluginModel;
    use Ramsey\Uuid\Uuid;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\Response;
    use Illuminate\Support\Collection;
    use Illuminate\Http\Client\Factory;

    /**
     *
     */
    class Plugins
    {
        /**
         * @var PluginRepository
         */
        private PluginRepository $pluginRepository;

        /**
         * @var string
         */

        private string $pluginDirectory =  ROOT . "/../app/Plugins/";
        /**
         * @var EnvironmentCore
         */
        private EnvironmentCore $config;

        /**
         * Plugin types
         * custom: Plugin is loaded as a folder, available under discover plugins
         * system: Plugin is defined in config and loaded on start. Cannot delete, or disable plugin
         * marketplace: Plugin comes from maarketplace.
         *
         * @var array
         */
        private array $pluginTypes = [
            'custom' => "custom",
            'system' => "system",
            'marketplace' => "marketplace",
        ];

        /**
         * Plugin formats
         * phar: Phar plugins (only from marketplace)
         * folder: Folder plugins
         *
         * @var array
         */
        private array $pluginFormat = [
            'phar' => 'phar',
            'folder' => 'phar',
        ];

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
        public function getAllPlugins(bool $enabledOnly = false): false|array
        {
            try {
                $installedPlugins = $this->pluginRepository->getAllPlugins($enabledOnly);
            } catch (\Exception $e) {
                $installedPlugins = [];
            }

            //Build array with pluginId as $key
            foreach ($installedPlugins as &$plugin) {
                if ($plugin->format === $this->pluginFormat["phar"]) {
                    $plugin->type = $this->pluginTypes["marketplace"];
                } else {
                    $plugin->type = $this->pluginTypes["custom"];
                }
                $installedPluginsById[$plugin->foldername] = $plugin;
            }

            // Gets plugins from the config, which are automatically enabled
            if (
                isset($this->config->plugins)
                && $configplugins = explode(',', $this->config->plugins)
            ) {
                foreach ($configplugins as $plugin) {
                    if ($plugin != '') {

                        $pluginModel = app()->make(PluginModel::class);
                        $pluginModel->foldername = $plugin;
                        $pluginModel->name = $plugin;
                        $pluginModel->format = file_exists(
                            $this->pluginDirectory . "/" . $plugin . "/." . $plugin . ".phar"
                        ) ? 'phar' : 'folder';
                        $pluginModel->type = $this->pluginTypes['system'];
                        $pluginModel->enabled = true;

                        if(isset($installedPluginsById[$plugin])) {
                            $installedPluginsById[$plugin]->enabled = true;
                            $installedPluginsById[$plugin]->type = $this->pluginTypes['system'];
                            $installedPluginsById[$plugin]->format = $pluginModel->format;
                        }else{
                            $installedPluginsById[$plugin] = $pluginModel;
                        }

                    }
                }
            }

            return $installedPluginsById;
        }

        /**
         * @param $pluginFolder
         * @return bool
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
        public function getEnabledPlugins(): mixed
        {

            unset($_SESSION['enabledPlugins']);
            if (isset($_SESSION['enabledPlugins'])) {
                return $_SESSION['enabledPlugins'];
            }

            $_SESSION['enabledPlugins'] = $this->getAllPlugins(enabledOnly: true);

            return $_SESSION['enabledPlugins'];
        }


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
                $plugin->license = ''; //TODO: Add license to install routine
                $plugin->format = file_exists($this->pluginDirectory . "/" . $pluginFolder . "/." . $pluginFolder . ".phar") ? 'phar' : 'folder';
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
        public function enablePlugin(int $id): bool
        {
            unset($_SESSION['enabledPlugins']);
            return $this->pluginRepository->enablePlugin($id);
        }

        /**
         * @param int $id
         * @return bool
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
                    $newPluginSvc->uninstall();
                } catch (\Exception $e) {
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
        public function getPluginClassName(PluginModel $plugin): string
        {
            return app()->getNamespace()
                . 'Plugins\\'
                . htmlspecialchars(ucfirst($plugin->foldername))
                . '\\Services\\'
                . htmlspecialchars(ucfirst($plugin->foldername));
        }

        /**
         * @param int    $page
         * @param string $query
         * @return Collection
         */
        public function getMarketplacePlugins(int $page, string $query = ''): Collection
        {
            $baseUrl = 'http://marketplace.leantime.local:8888/ltmp-api';
            #$baseUrl = 'https://marketplace.leantime.io/ltmp-api';

            $plugins = ! empty($query)
                ? Http::get("$baseUrl/search/$query/$page")
                : Http::get("$baseUrl/index/$page");

            return $plugins->collect();
        }

        /**
         * @param string $identifier
         * @return Collection
         */
        public function getMarketplacePlugin(string $identifier): Collection
        {
            $baseUrl = 'http://marketplace.leantime.local:8888/ltmp-api';
            #$baseUrl = 'https://marketplace.leantime.io/ltmp-api';

            return Http::get("$baseUrl/versions/$identifier")->collect();
        }
    }
}
