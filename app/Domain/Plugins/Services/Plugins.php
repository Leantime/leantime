<?php

namespace Leantime\Domain\Plugins\Services {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Core\Eventhelpers;
    use Leantime\Domain\Plugins\Models\MarketplacePlugin;
    use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
    use Leantime\Domain\Plugins\Models\InstalledPlugin;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\RequestException;
    use Leantime\Domain\Settings\Repositories\Settings as SettingsRepository;

    /**
     *
     */
    class Plugins
    {

        use Eventhelpers;
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
         * Marketplace URL
         *
         * @var string
         */
        private string $marketplaceUrl = "http://marketplace.leantime.local:8888/ltmp-api";

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
            $installedPluginsById = [];

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

                        $pluginModel = app()->make(InstalledPlugin::class);
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

            /**
             * Filters array of plugins from database and config before returning
             * @var array $allPlugins
             */
            $allPlugins = static::dispatch_filter("beforeReturnAllPlugins", $installedPluginsById, array("enabledOnly" => $enabledOnly));

            return $allPlugins;
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

            if (isset($_SESSION['enabledPlugins'])) {

                $enabledPlugins = static::dispatch_filter("beforeReturnCachedPlugins", $_SESSION['enabledPlugins'], array("enabledOnly" => true));
                return $enabledPlugins;
            }

            $_SESSION['enabledPlugins'] = $this->getAllPlugins(enabledOnly: true);

            /**
             * Filters session array of enabled plugins before returning
             * @var array $enabledPlugins
             */
            $enabledPlugins = static::dispatch_filter("beforeReturnCachedPlugins", $_SESSION['enabledPlugins'], array("enabledOnly" => true));
            return $enabledPlugins;
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
                        $plugin = app()->make(InstalledPlugin::class);
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
                $plugin = app()->make(InstalledPlugin::class);
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
         * @return MarketplacePlugin[]
         */
        public function getMarketplacePlugins(int $page, string $query = ''): array
        {
            $plugins = Http::withoutVerifying()->get(
                "$this->marketplaceUrl"
                . (! empty($query) ? "/search/$query" : '/index')
                . "/$page"
            );

            $pluginArray = $plugins->collect()->toArray();

            $plugins = [];

            if(isset($pluginArray["data"] )) {

                foreach ($pluginArray["data"] as $plugin) {
                    $pluginModel = app()->make(MarketplacePlugin::class);
                    $pluginModel->identifier = $plugin['identifier'];
                    $pluginModel->name = $plugin['post_title'];
                    $pluginModel->excerpt = $plugin['excerpt'];
                    $pluginModel->imageUrl = $plugin['featured_image'];
                    $pluginModel->authors = ''; //TODO Send from marketplace
                    $plugins[] = $pluginModel;
                }
            }

            return $plugins;
        }

        /**
         * @param string $identifier
         * @return MarketplacePlugin[]
         */
        public function getMarketplacePlugin(string $identifier): array
        {
            return Http::get("$this->marketplaceUrl/versions/$identifier")
                ->collect()
                ->mapWithKeys(function ($data, $version) use ($identifier) {
                    static $count;
                    $count ??= 0;

                    $pluginModel = app()->make(MarketplacePlugin::class);
                    $pluginModel->identifier = $identifier;
                    $pluginModel->name = $data['name'];
                    $pluginModel->excerpt = '';
                    $pluginModel->description = $data['description'];
                    $pluginModel->marketplaceUrl = $data['marketplace_url'];
                    $pluginModel->thumbnailUrl = $data['thumbnail_url'] ?: '';
                    $pluginModel->authors = $data['author'];
                    $pluginModel->version = $version;
                    $pluginModel->price = $data['price'];
                    $pluginModel->license = $data['license'];
                    $pluginModel->rating = $data['rating'];
                    $pluginModel->marketplaceId = $data['product_id'];

                    return [$count++ => $pluginModel];
                })
                ->all();
        }

        /**
         * @param MarketplacePlugin $plugin
         * @return void
         * @throws Illuminate\Http\Client\RequestException|Exception
         */
        public function installMarketplacePlugin(MarketplacePlugin $plugin): void
        {
            $response = Http::withHeaders([
                    'X-License-Key' => $plugin->license,
                    'X-Instance-Id' => app()
                        ->make(SettingsRepository::class)
                        ->getSetting('companysettings.telemetry.anonymousId')
                ])
                ->get("{$this->marketplaceUrl}/download/{$plugin->marketplaceId}");

            $response->throwIf(in_array(true, [
                ! $response->ok(),
                $response->header('Content-Type') !== 'application/zip'
            ]), fn () => new RequestException($response));

            $filename = $response->header('Content-Disposition');
            $filename = substr($filename, strpos($filename, 'filename=') + 1);

            if (! str_starts_with($filename, $plugin->identifier)) {
                throw new \Exception('Wrong file downloaded');
            }

            if (! file_put_contents(
                $temporaryFile = sys_get_temp_dir() . '/' . $filename,
                $response->body()
            )) {
                throw new \Exception('Could not download plugin');
            }

            if (is_dir($pluginDir = APP_ROOT . '/app/Plugins/' . $plugin->identifier)) {
                rmdir($pluginDir);
            }
            mkdir($pluginDir);

            $zip = new \ZipArchive();
            $zip->open($temporaryFile);
            $zip->extractTo($pluginDir);
            $zip->close();

            unlink($temporaryFile);

            $pluginModel = app()->make(InstalledPlugin::class);
            $pluginModel->name = $plugin->name;
            $pluginModel->enabled = 0;
            $pluginModel->description = $plugin->excerpt;
            $pluginModel->version = $plugin->version;
            $pluginModel->installdate = date("Y-m-d");
            $pluginModel->foldername = $plugin->identifier;
            $pluginModel->homepage = $plugin->marketplaceUrl;
            $pluginModel->authors = $plugin->authors;
            $pluginModel->license = $plugin->license;
            $pluginModel->format = 'phar';

            $this->pluginRepository->addPlugin($pluginModel);
        }
    }
}
