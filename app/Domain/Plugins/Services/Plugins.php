<?php

namespace Leantime\Domain\Plugins\Services {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Http\Client\RequestException;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Str;
    use Leantime\Core\Configuration\Environment as EnvironmentCore;
    use Leantime\Core\Events\DispatchesEvents;
    use Leantime\Domain\Plugins\Models\InstalledPlugin;
    use Leantime\Domain\Plugins\Models\MarketplacePlugin;
    use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
    use Leantime\Domain\Setting\Services\Setting as SettingsService;
    use Leantime\Domain\Users\Services\Users as UsersService;

    /**
     *
     *
     * @api
     */
    class Plugins
    {
        use DispatchesEvents;

        /**
         * @var string
         *
     * @api
     */
        private string $pluginDirectory =  ROOT . "/../app/Plugins/";

        /**
         * Plugin types
         * custom: Plugin is loaded as a folder, available under discover plugins
         * system: Plugin is defined in config and loaded on start. Cannot delete, or disable plugin
         * marketplace: Plugin comes from maarketplace.
         *
         * @var array
         *
     * @api
     */
        private array $pluginTypes = [
            'custom' => "custom",
            'system' => "system",
            'marketplace' => 'marketplace',
        ];

        /**
         * Plugin formats
         * phar: Phar plugins (only from marketplace)
         * folder: Folder plugins
         *
         * @var array
         *
     * @api
     */
        private array $pluginFormat = [
            'phar' => 'phar',
            'folder' => 'phar',
        ];

        /**
         * Marketplace URL
         *
         * @var string
         *
     * @api
     */
        public string $marketplaceUrl;

        /**
         * @param PluginRepository $pluginRepository
         * @param EnvironmentCore  $config
         * @param SettingsService  $settingsService
         * @param UsersService     $usersService
         * @return void
         * @throws BindingResolutionException
         **/
        public function __construct(
            private PluginRepository $pluginRepository,
            private EnvironmentCore $config,
            private SettingsService $settingsService,
            private UsersService $usersService,
        ) {
            $this->marketplaceUrl = rtrim($config->marketplaceUrl, '/');
        }

        /**
         * @return array|false
         *
     * @api
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
                $plugin->type = $plugin->format === $this->pluginFormat['phar']
                    ? $plugin->type = $this->pluginTypes['marketplace']
                    : $plugin->type = $this->pluginTypes['custom'];

                $installedPluginsById[$plugin->foldername] = $plugin;

            }

            // Gets plugins from the config, which are automatically enabled
            if (
                isset($this->config->plugins)
                && $configplugins = explode(',', $this->config->plugins)
            ) {
                collect($configplugins)
                ->filter(fn ($plugin) => ! empty($plugin))
                ->each(function ($plugin) use (&$installedPluginsById) {

                    try {
                        $pluginModel = $this->createPluginFromComposer($plugin);

                        $installedPluginsById[$plugin] ??= $pluginModel;
                        $installedPluginsById[$plugin]->enabled = true;
                        $installedPluginsById[$plugin]->type = $this->pluginTypes['system'];
                    } catch (Exception $e) {
                        report($e);
                    }
                });
            }

            /**
             * Filters array of plugins from database and config before returning
             * @var array $allPlugins
             *
             */
            $allPlugins = self::dispatch_filter("beforeReturnAllPlugins", $installedPluginsById, array("enabledOnly" => $enabledOnly));

            return $allPlugins;
        }

        /**
         * @param $pluginFolder
         * @return bool
         * @throws BindingResolutionException
         *
     * @api
     */
        public function isEnabled($pluginFolder): bool
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
         *
         * @api
         */
        public function getEnabledPlugins(): mixed
        {

            if (Cache::store("installation")->has("enabledPlugins")) {
                $enabledPlugins = static::dispatch_filter("beforeReturnCachedPlugins", Cache::store("installation")->get("enabledPlugins"), array("enabledOnly" => true));
                return $enabledPlugins;
            }

            Cache::store("installation")->set("enabledPlugins", $this->getAllPlugins(enabledOnly: true));

            /**
             * Filters session array of enabled plugins before returning
             * @var array $enabledPlugins
             *
             */
            return self::dispatch_filter(
                hook: "beforeReturnCachedPlugins",
                payload: Cache::store("installation")->get("enabledPlugins"),
                available_params: array("enabledOnly" => true));

        }

        /**
         * @return InstalledPlugin[]
         * @throws BindingResolutionException
         *
     * @api
     */
        public function discoverNewPlugins(): array
        {
            $this->clearCache();

            $installedPluginNames = array_map(fn ($plugin) => $plugin->foldername, $this->getAllPlugins());
            $scanned_directory = array_diff(scandir($this->pluginDirectory), ['..', '.']);

            $newPlugins = collect($scanned_directory)
                ->filter(fn ($directory) => is_dir("{$this->pluginDirectory}/{$directory}") && ! array_search($directory, $installedPluginNames))
                ->map(function ($directory) {
                    try {
                        return $this->createPluginFromComposer($directory);
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter()->all();

            return $newPlugins;
        }

        public function createPluginFromComposer(string $pluginFolder, string $license_key = ''): InstalledPlugin
        {
            $pluginPath = Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR) . Str::finish($pluginFolder, DIRECTORY_SEPARATOR);

            if (file_exists($composerPath = $pluginPath . 'composer.json')) {
                $format = 'folder';
            } elseif (file_exists($composerPath = "phar://{$pluginPath}{$pluginFolder}.phar" . DIRECTORY_SEPARATOR . 'composer.json')) {
                $format = 'phar';
            } else {
                throw new \Exception(__('notifications.plugin_install_cant_find_composer'));
            }

            $json = file_get_contents($composerPath);
            $pluginFile = json_decode($json, true);

            $plugin = build(new InstalledPlugin())
                ->set('name', $pluginFile['name'])
                ->set('enabled', 0)
                ->set('description', $pluginFile['description'])
                ->set('version', $pluginFile['version'])
                ->set('installdate', date("y-m-d"))
                ->set('foldername', $pluginFolder)
                ->set('license', $license_key)
                ->set('format', $format)
                ->set('homepage', $pluginFile['homepage'])
                ->set('authors', json_encode($pluginFile['authors']))
                ->get();

            return $plugin;
        }

        /**
         * @param $pluginFolder
         * @return false|string
         * @throws BindingResolutionException
         *
     * @api
     */
        public function installPlugin($pluginFolder): false|string
        {
            $this->clearCache();

            $pluginFolder = Str::studly($pluginFolder);

            try {
                $plugin = $this->createPluginFromComposer($pluginFolder);
            } catch (\Exception $e) {
                report($e);
                return false;
            }

            $pluginClassName = $this->getPluginClassName($plugin);
            $newPluginSvc = app()->make($pluginClassName);

            if (method_exists($newPluginSvc, "install")) {
                try {
                    $newPluginSvc->install();
                } catch (Exception $e) {
                    report($e);
                    return false;
                }
            }

            return $this->pluginRepository->addPlugin($plugin);
        }

        /**
         * @param int $id
         * @return bool
         *
         * @api
         */
        public function enablePlugin(int $id): bool
        {
            $this->clearCache();

            $pluginModel = $this->pluginRepository->getPlugin($id);

            if ($pluginModel->format == 'phar') {
                $phar = new \Phar(
                    Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR)
                    . Str::finish($pluginModel->foldername, DIRECTORY_SEPARATOR)
                    . Str::finish($pluginModel->foldername, '.phar')
                );

                $signature = $phar->getSignature();

                $response = Http::withoutVerifying()->get("$this->marketplaceUrl", [
                    'wp-api' => 'software-api',
                    'request' => 'activation',
                    'license_key' => $pluginModel->license,
                    'product_id' => $pluginModel->id,
                    'instance' => $this->settingsService->getCompanyId(),
                    'phar_hash' => $signature,
                    'user_count' => $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false),
                ]);

                $content = $response->body();

                if (! $response->ok()) {
                    return false;
                }
            }

            return $this->pluginRepository->enablePlugin($id);
        }

        /**
         * @param int $id
         * @return bool
         *
     * @api
     */
        public function disablePlugin(int $id): bool
        {
            $this->clearCache();

            $pluginModel = $this->pluginRepository->getPlugin($id);

            if ($pluginModel->format == 'phar') {
                $phar = new \Phar(
                    Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR)
                    . Str::finish($pluginModel->foldername, DIRECTORY_SEPARATOR)
                    . Str::finish($pluginModel->foldername, '.phar')
                );

                $signature = $phar->getSignature();

                $response = Http::get("$this->marketplaceUrl", [
                    'request' => 'deactivation',
                    'license_key' => $pluginModel->license,
                    'product_id' => $pluginModel->id,
                    'instance' => $this->settingsService->getCompanyId(),
                    'phar_hash' => $signature,
                    'user_count' => $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false),
                ]);

                if (! $response->ok()) {
                    return false;
                }
            }

            return $this->pluginRepository->disablePlugin($id);
        }

        /**
         * @param int $id
         * @return bool
         * @throws BindingResolutionException
         *
     * @api
     */
        public function removePlugin(int $id): bool
        {
            $this->clearCache();

            /** @var PluginModel|false $plugin */
            $plugin = $this->pluginRepository->getPlugin($id);

            if (! $plugin) {
                return false;
            }

            try {
                //Any installation calls should happen right here.
                $pluginClassName = $this->getPluginClassName($plugin);
                $newPluginSvc = app()->make($pluginClassName);

                if (method_exists($newPluginSvc, "uninstall")) {
                    try {
                        $newPluginSvc->uninstall();
                    } catch (\Exception $e) {
                        report($e);
                        return false;
                    }
                }
            } catch (\Exception $e) {
                //Silence is golden
            }

            return $this->pluginRepository->removePlugin($id);

            //TODO remove files savely
        }

        /**
         * @param InstalledPlugin $plugin
         * @return string
         * @throws BindingResolutionException
         *
     * @api
     */
        public function getPluginClassName(InstalledPlugin $plugin): string
        {
            return app()->getNamespace()
                . 'Plugins\\'
                . Str::studly($plugin->foldername)
                . '\\Services\\'
                . Str::studly($plugin->foldername);
        }

        /**
         * @param int    $page
         * @param string $query
         * @return MarketplacePlugin[]
         *
     * @api
     */
        public function getMarketplacePlugins(int $page, string $query = ''): array
        {
            $plugins = Http::withoutVerifying()->get(
                "{$this->marketplaceUrl}/ltmp-api"
                . (! empty($query) ? "/search/$query" : '/index')
                . "/$page"
            );

            $pluginArray = $plugins->collect()->toArray();

            $plugins = [];

            if (isset($pluginArray["data"])) {
                foreach ($pluginArray["data"] as $plugin) {
                    $plugins[] = build(new MarketplacePlugin())
                        ->set('identifier', $plugin['identifier'] ?? '')
                        ->set('name', $plugin['post_title'] ?? '')
                        ->set('excerpt', $plugin['excerpt'] ?? '')
                        ->set('imageUrl', $plugin['icon'] ?? '')
                        ->set('vendorDisplayName', $plugin['vendor'] ?? '')
                        ->set('vendorId', $plugin['vendor_id'] ?? '')
                        ->set('vendorEmail', $plugin['vendor_email'] ?? '')
                        ->set('startingPrice', '$' . ($plugin['price'] ?? '') . (! empty($plugin['sub_interval']) ? '/' . $plugin['sub_interval'] : ''))
                        ->set('rating', $plugin['rating'] ?? '')
                        ->set('version', $plugin['version'] ?? '')
                        ->get();
                }
            }

            return $plugins;
        }

        /**
         * @param string $identifier
         * @return MarketplacePlugin[]
         *
     * @api
     */
        public function getMarketplacePlugin(string $identifier): MarketplacePlugin|false
        {
            $response = Http::withoutVerifying()->get("$this->marketplaceUrl/ltmp-api/details/$identifier");

            if (! $response->ok()) {
                return false;
            }

            $data = $response->json();

            return build(new MarketplacePlugin())
                ->set('identifier', $identifier)
                ->set('name', $data['name'] ?? '')
                ->set('icon', $data['icon'] ?? '')
                ->set('description', nl2br($data['description'] ?? ''))
                ->set('marketplaceUrl', $data['marketplaceUrl'] ?? '')
                ->set('vendorId', (int) $data['vendor']['id'] ?? null)
                ->set('vendorDisplayName', $data['vendor']['name'] ?? '')
                ->set('rating', $data['reviews']['average'] ?? 'N/A')
                ->set('reviewCount', $data['reviews']['count'] ?? 0)
                ->set('reviews', $data['reviews']['list'])
                ->set('marketplaceId', $data['productId'])
                ->set('pricingTiers', $data['tiers'])
                ->set('categories', $data['categories'] ?? [])
                ->set('tags', $data['tags'] ?? [])
                ->set('compatibility', $data['compatibility'] ?? [])
                ->get();
        }

        /**
         * @param MarketplacePlugin $plugin
         * @param string            $version
         * @return void
         * @throws Illuminate\Http\Client\RequestException|Exception
         *
     * @api
     */
        public function installMarketplacePlugin(MarketplacePlugin $plugin, string $version): void
        {

            $this->clearCache();

            $response = Http::withoutVerifying()->withHeaders([
                    'X-License-Key' => $plugin->license,
                    'X-Instance-Id' => $this->settingsService->getCompanyId(),
                    'X-User-Count' => $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false),
                ])
                ->get("{$this->marketplaceUrl}/ltmp-api/download/{$plugin->identifier}/{$version}");

            if (!$response->ok()) {
                throw new RequestException($response);
            }

            if ($response->header('Content-Type') !== 'application/zip') {
                throw new RequestException($response);
            }

            $filename = $response->header('Content-Disposition');
            $filename = substr($filename, strpos($filename, 'filename=') + 9);
            $foldername = Str::studly(basename($filename, '.zip'));
            $filename = Str::finish($foldername, '.zip');

            if (
                ! file_put_contents(
                    $temporaryFile = Str::finish(sys_get_temp_dir(), '/') . $filename,
                    $response->body()
                )
            ) {
                throw new \Exception(__('notification.plugin_cant_download'));
            }

            if (
                is_dir($pluginDir = "{$this->pluginDirectory}{$foldername}")
                && ! File::deleteDirectory($pluginDir)
            ) {
                throw new \Exception(__('notification.plugin_cant_remove'));
            }

            mkdir($pluginDir);

            $zip = new \ZipArchive();

            match ($zip->open($temporaryFile)) {
                \ZipArchive::ER_EXISTS => throw new \Exception(__('notification.plugin_zip_exists')),
                \ZipArchive::ER_INCONS => throw new \Exception(__('notification.plugin_zip_inconsistent')),
                \ZipArchive::ER_INVAL => throw new \Exception(__('notification.plugin_zip_invalid_arg')),
                \ZipArchive::ER_MEMORY => throw new \Exception(__('notification.plugin_zip_malloc')),
                \ZipArchive::ER_NOENT => throw new \Exception(__('notification.plugin_zip_no_file')),
                \ZipArchive::ER_NOZIP => throw new \Exception(__('notification.plugin_zip_not_zip')),
                \ZipArchive::ER_OPEN => throw new \Exception(__('notification.plugin_zip_cant_open')),
                \ZipArchive::ER_READ => throw new \Exception(__('notification.plugin_zip_read_err')),
                \ZipArchive::ER_SEEK => throw new \Exception(__('notification.plugin_zip_seek_err')),
                default => throw new \Exception(__('notification.plugin_zip_unknown_err')),
                true => null,
            };

            if (! $zip->extractTo($pluginDir)) {
                throw new \Exception(__('notification.plugin_zip_cant_extract'));
            }

            $zip->close();

            unlink($temporaryFile);

            # read the composer.json content from the plugin phar file
            $pluginModel = $this->createPluginFromComposer($foldername, $plugin->license);

            if (! $this->pluginRepository->addPlugin($pluginModel)) {
                throw new \Exception(__('notification_cant_add_to_db'));
            }
        }

        public function canActivate(InstalledPlugin $plugin): bool
        {
            if ($plugin->type !== $this->pluginTypes['marketplace']) {
                return true;
            }

            $numberOfUsers = $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false);
            $instanceId = $this->settingsService->getCompanyId();

            $response = Http::withoutVerifying()->get($this->marketplaceUrl, [
                'wp-api' => 'software-api',
                'request' => 'check',
                'product_id' => $plugin->id,
                'license_key' => $plugin->license,
                'instance' => $instanceId,
                'user_count' => $numberOfUsers,
            ]);

            if (! $response->ok()) {
                return false;
            }

            return true;
        }

        public function clearCache()
        {

            Cache::store('installation')->forget("domainEvents");
            Cache::store('installation')->forget("commands");
            Cache::store('installation')->forget("enabledPlugins");

            session()->forget("template_paths");
            session()->forget("composers");
        }
    }
}
