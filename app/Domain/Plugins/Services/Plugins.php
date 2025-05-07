<?php

namespace Leantime\Domain\Plugins\Services;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Notifications\Services\Notifications;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Users\Services\Users as UsersService;
use Leantime\Infrastructure\Console\ConsoleKernel;

/**
 * @api
 */
class Plugins
{
    use DispatchesEvents;

    private string $pluginDirectory = ROOT.'/../app/Plugins/';

    /**
     * Plugin types
     * custom: Plugin is loaded as a folder, available under discover plugins
     * system: Plugin is defined in config and loaded on start. Cannot delete, or disable plugin
     * marketplace: Plugin comes from maarketplace.
     */
    private array $pluginTypes = [
        'custom' => 'custom',
        'system' => 'system',
        'marketplace' => 'marketplace',
    ];

    /**
     * Plugin formats
     * phar: Phar plugins (only from marketplace)
     * folder: Folder plugins
     */
    private array $pluginFormat = [
        'phar' => 'phar',
        'folder' => 'phar',
    ];

    public string $marketplaceUrl;

    /**
     * @return void
     *
     * @throws BindingResolutionException
     **/
    public function __construct(
        private PluginRepository $pluginRepository,
        private EnvironmentCore $config,
        private SettingsService $settingsService,
        private UsersService $usersService,
        private ConsoleKernel $leantimeCli,
        private AppSettings $appSettings,
    ) {
        $this->marketplaceUrl = rtrim($config->marketplaceUrl, '/');
        // $this->marketplaceUrl = 'https://marketplace.leantime.test';
    }

    /**
     * Retrieves all plugins, optionally filtering only the enabled ones.
     *
     * @param  bool  $enabledOnly  If set to true, only enabled plugins will be returned.
     * @return false|array<InstalledPlugin> Returns an array of all plugins or false if an error occurs.
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

        // Build array with pluginId as $key
        foreach ($installedPlugins as &$plugin) {

            /** @var array<MarketplacePlugin> */
            $marketplacePluginCache = Cache::store('installation')->get('plugins.marketplacePluginsFlat', false);

            $plugin->type = $plugin->format === $this->pluginFormat['phar']
                ? $plugin->type = $this->pluginTypes['marketplace']
                : $plugin->type = $this->pluginTypes['custom'];

            // Make installed plugins pretty
            $pluginIdentifier = Str::replace('/', '_', Str::lower($plugin->name));
            if ($marketplacePluginCache && isset($marketplacePluginCache[$pluginIdentifier])) {
                $plugin->identifier = $marketplacePluginCache[$pluginIdentifier]->identifier;
                $plugin->name = $marketplacePluginCache[$pluginIdentifier]->name;
                $plugin->imageUrl = $marketplacePluginCache[$pluginIdentifier]->imageUrl;
                $plugin->description = $marketplacePluginCache[$pluginIdentifier]->excerpt;
                $plugin->vendorDisplayName = $marketplacePluginCache[$pluginIdentifier]->vendorDisplayName;
                $plugin->vendorId = $marketplacePluginCache[$pluginIdentifier]->vendorId;
                $plugin->vendorEmail = $marketplacePluginCache[$pluginIdentifier]->vendorEmail;
            }
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
         *
         * @var array $allPlugins
         */
        $allPlugins = self::dispatch_filter('beforeReturnAllPlugins', $installedPluginsById, ['enabledOnly' => $enabledOnly]);

        return $allPlugins;
    }

    /**
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
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getEnabledPlugins(): mixed
    {

        if (Cache::store('installation')->has('plugins.enabledPlugins')) {
            $enabledPlugins = static::dispatch_filter('beforeReturnCachedPlugins', Cache::store('installation')->get('plugins.enabledPlugins'), ['enabledOnly' => true]);

            return $enabledPlugins;
        }

        Cache::store('installation')->set('plugins.enabledPlugins', $this->getAllPlugins(enabledOnly: true));

        /**
         * Filters session array of enabled plugins before returning
         *
         * @var array $enabledPlugins
         */
        return self::dispatch_filter(
            hook: 'beforeReturnCachedPlugins',
            payload: Cache::store('installation')->get('plugins.enabledPlugins'),
            available_params: ['enabledOnly' => true]);

    }

    /**
     * @return InstalledPlugin[]
     *
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
        $pluginPath = Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR).Str::finish($pluginFolder, DIRECTORY_SEPARATOR);

        if (file_exists($composerPath = $pluginPath.'composer.json')) {
            $format = 'folder';
        } elseif (file_exists($composerPath = "phar://{$pluginPath}{$pluginFolder}.phar".DIRECTORY_SEPARATOR.'composer.json')) {
            $format = 'phar';
        } else {
            throw new \Exception(__('notifications.plugin_install_cant_find_composer'));
        }

        $json = file_get_contents($composerPath);
        $pluginFile = json_decode($json, true);

        $plugin = build(new InstalledPlugin)
            ->set('name', $pluginFile['name'])
            ->set('enabled', 0)
            ->set('description', $pluginFile['description'])
            ->set('version', $pluginFile['version'])
            ->set('installdate', date('y-m-d'))
            ->set('foldername', $pluginFolder)
            ->set('license', $license_key)
            ->set('format', $format)
            ->set('homepage', $pluginFile['homepage'])
            ->set('authors', json_encode($pluginFile['authors']))
            ->get();

        return $plugin;
    }

    /**
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

        if (method_exists($newPluginSvc, 'install')) {
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
     * @api
     */
    public function enablePlugin(int $id): bool
    {
        $this->clearCache();

        $pluginModel = $this->pluginRepository->getPlugin($id);

        if ($pluginModel->format !== 'phar') {
            return $this->pluginRepository->enablePlugin($id);
        }

        if ($this->validLicense($pluginModel)) {
            return $this->pluginRepository->enablePlugin($id);
        }

        return false;

    }

    /**
     * @api
     */
    public function disablePlugin(int $id): bool
    {
        $this->clearCache();

        $pluginModel = $this->pluginRepository->getPlugin($id);

        $result = $this->pluginRepository->disablePlugin($id);

        if ($pluginModel->format === 'phar') {

            $this->deactivate($pluginModel);

        }

        return $result;
    }

    /**
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
            // Any installation calls should happen right here.
            $pluginClassName = $this->getPluginClassName($plugin);
            $newPluginSvc = app()->make($pluginClassName);

            if (method_exists($newPluginSvc, 'uninstall')) {
                try {
                    $newPluginSvc->uninstall();
                } catch (\Exception $e) {
                    report($e);

                    return false;
                }
            }
        } catch (\Exception $e) {
            // Silence is golden
        }

        return $this->pluginRepository->removePlugin($id);

    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getPluginClassName(InstalledPlugin $plugin): string
    {
        return app()->getNamespace()
            .'Plugins\\'
            .Str::studly($plugin->foldername)
            .'\\Services\\'
            .Str::studly($plugin->foldername);
    }

    /**
     * Fetches marketplace plugins from a specified URL and organizes them by category.
     *
     * @param  int  $page  The page number to fetch.
     * @param  string  $query  Optional search query to filter plugins by name or identifier.
     * @return array An associative array containing plugin categories, each with associated plugins.
     *               The categories include metadata such as name, description, and plugins categorized under them.
     */
    public function getMarketplacePlugins(int $page, string $query = ''): array
    {
        $plugins = Http::withoutVerifying()->get(
            "{$this->marketplaceUrl}/ltmp-api"
            .(! empty($query) ? "/search/$query" : '/index')
            ."/$page".'?lt-v='.$this->appSettings->appVersion.'&groupBy=category'
        );

        $pluginArray = $plugins->collect()->toArray();

        $plugins = [];
        $pluginsFlat = [];

        if (isset($pluginArray['categories'])) {
            foreach ($pluginArray['categories'] as $category) {

                $plugins[$category['slug']] = [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'plugins' => [],

                ];

                foreach ($category['products'] as $plugin) {
                    $priceString = '';
                    if (! empty($plugin['sub_interval']) && $plugin['sub_interval'] === 'year') {
                        $price = $plugin['price'] ?? 0;
                        $months = 12;
                        $lowestUserTier = 10;
                        $perUserMonth = round(($price / $months / $lowestUserTier), 2);
                        $priceString = '$'.$perUserMonth.' per user/month (billed annually) <a href="javascript:void(0)" data-tippy-content="10 user minimum, billed annually"><i class="fa fa-circle-info"></i></a>';
                    }

                    $plugins[$category['slug']]['plugins'][Str::lower($plugin['identifier'])] = build(new MarketplacePlugin)
                        ->set('identifier', $plugin['identifier'] ?? '')
                        ->set('name', $plugin['post_title'] ?? '')
                        ->set('excerpt', $plugin['excerpt'] ?? '')
                        ->set('imageUrl', $plugin['icon'] ?? '')
                        ->set('vendorDisplayName', $plugin['vendor'] ?? '')
                        ->set('vendorId', $plugin['vendor_id'] ?? '')
                        ->set('vendorEmail', $plugin['vendor_email'] ?? '')
                        ->set(
                            'startingPrice',
                            '$'.($plugin['price'] ?? '').(! empty($plugin['sub_interval']) ? '/'.$plugin['sub_interval'] : '')
                        )
                        ->set('calculatedMonthlyPrice', $priceString)
                        ->set('rating', $plugin['rating'] ?? '')
                        ->set('version', $plugin['version'] ?? '')
                        ->get();

                    $pluginsFlat[Str::lower($plugin['identifier'])] = $plugins[$category['slug']]['plugins'][Str::lower($plugin['identifier'])];
                }
            }
        }

        Cache::store('installation')->set('plugins.marketplacePlugins', $plugins);
        Cache::store('installation')->set('plugins.marketplacePluginsFlat', $pluginsFlat);

        return $plugins;
    }

    /**
     * Retrieves a marketplace plugin's details by its identifier.
     *
     * @param  string  $identifier  The unique identifier of the marketplace plugin.
     * @return MarketplacePlugin|false Returns a MarketplacePlugin instance if found, or false if the plugin could not be retrieved.
     *
     * @api
     */
    public function getMarketplacePlugin(string $identifier): MarketplacePlugin|false
    {
        $response = Http::withoutVerifying()->get("$this->marketplaceUrl/ltmp-api/details/$identifier?lt-v=".$this->appSettings->appVersion);

        if (! $response->ok()) {
            return false;
        }

        $data = $response->json();

        return build(new MarketplacePlugin)
            ->set('identifier', $identifier)
            ->set('name', $data['name'] ?? '')
            ->set('icon', $data['icon'] ?? '')
            ->set('description', nl2br($data['description'] ?? ''))
            ->set('marketplaceUrl', $data['marketplaceUrl'] ?? '')
            ->set('vendorId', (int) ($data['vendor']['id'] ?? null))
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
     * Installs a marketplace plugin by downloading, extracting, and registering it in the plugin repository.
     *
     * @param  MarketplacePlugin  $plugin  The marketplace plugin to be installed, including its identifier and license key.
     * @param  string  $version  The version of the plugin to be installed.
     *
     * @throws RequestException If the HTTP request to download the plugin fails or returns an unexpected response type.
     * @throws \Exception If the plugin cannot be downloaded, removed, or added to the system.
     * @throws \RuntimeException If the plugin directory cannot be created.
     */
    public function installMarketplacePlugin(MarketplacePlugin $plugin, string $version): void
    {

        $this->clearCache();

        $response = Http::withoutVerifying()->withHeaders([
            'X-License-Key' => $plugin->license,
            'X-Instance-Id' => $this->settingsService->getCompanyId(),
            'X-User-Count' => $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false),
            'X-Leantime-Version' => $this->appSettings->appVersion,
        ])->get("{$this->marketplaceUrl}/ltmp-api/download/{$plugin->identifier}/{$version}");

        if (! $response->ok()) {
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
                $temporaryFile = Str::finish(sys_get_temp_dir(), '/').$filename,
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

        if (! mkdir($pluginDir) && ! is_dir($pluginDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $pluginDir));
        }

        $zip = new \ZipArchive;

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

        // read the composer.json content from the plugin phar file
        $pluginModel = $this->createPluginFromComposer($foldername, $plugin->license);

        if (! $this->pluginRepository->addPlugin($pluginModel)) {
            throw new \Exception(__('notification_cant_add_to_db'));
        }
    }

    /**
     * Validates the license of a given plugin.
     *
     * @param  InstalledPlugin  $plugin  The plugin object for which the license validity is being checked.
     * @return bool Returns true if the license is valid or the plugin is not of the marketplace type; returns false otherwise.
     */
    public function validLicense(InstalledPlugin $plugin): bool
    {

        if ($plugin->getType() !== $this->pluginTypes['marketplace']) {
            return true;
        }

        $numberOfUsers = $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false);
        $instanceId = $this->settingsService->getCompanyId();

        $phar = new \Phar(
            Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR)
            .Str::finish($plugin->foldername, DIRECTORY_SEPARATOR)
            .Str::finish($plugin->foldername, '.phar')
        );

        $signature = $phar->getSignature();

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'X-License-Key' => $plugin->license,
                'X-Instance-Id' => $instanceId,
                'X-User-Count' => $numberOfUsers,
                'X-Phar-Hash' => $signature,
                'X-Leantime-Version' => $this->appSettings->appVersion,
            ])->get("{$this->marketplaceUrl}/ltmp-api/verify/{$plugin->getIdentifier()}");

        } catch (\Exception $e) {
            Log::error($e);
        }

        $jsonResult = [];

        try {
            $body = $response->getBody()->getContents();
            $jsonResult = json_decode($body, true);
        } catch (Exception $e) {
            Log::error($e);
        }

        if ($response->ok() && $jsonResult['valid'] === false) {

            // Notify owners of system
            $this->disablePluginNotifyOwner($plugin->id);

            return false;
        }

        return true;
    }

    /**
     * Disables the specified plugin and notifies the owner or administrators of the action.
     *
     * @param  int  $pluginId  The ID of the plugin to disable.
     * @return void
     */
    public function disablePluginNotifyOwner($pluginId)
    {
        $this->clearCache();
        $this->disablePlugin($pluginId);

        // Get all admin users
        $userService = app()->make(UsersService::class);
        $notificationService = app()->make(Notifications::class);
        $plugin = $this->pluginRepository->getPlugin($pluginId);

        // Create notification for all admin users
        $admins = collect($userService->getAll())->filter(fn ($user) => in_array($user['role'], [40, 50]));

        $notifications = $admins->map(fn ($admin) => [
            'userId' => $admin['id'],
            'read' => '0',
            'type' => 'plugin_license',
            'module' => 'plugins',
            'moduleId' => $pluginId,
            'message' => sprintf("The plugin '%s' has been disabled due to license validation failure. Please check your marketplace subscription.", $plugin->name),
            'datetime' => date('Y-m-d H:i:s'),
            'url' => '/plugins/show',
            'authorId' => 1,
        ])->toArray();

        $notificationService->addNotifications($notifications);
    }

    /**
     * Deactivates the specified plugin by performing necessary operations
     * such as sending a request to the marketplace and validating the response.
     *
     * @param  InstalledPlugin  $plugin  The plugin instance to be deactivated.
     * @return bool Returns true if the plugin was successfully deactivated
     *              or does not require deactivation; false otherwise.
     */
    public function deactivate(InstalledPlugin $plugin): bool
    {
        if ($plugin->getType() !== $this->pluginTypes['marketplace']) {
            return true;
        }

        $numberOfUsers = $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false);
        $instanceId = $this->settingsService->getCompanyId();

        $phar = new \Phar(
            Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR)
            .Str::finish($plugin->foldername, DIRECTORY_SEPARATOR)
            .Str::finish($plugin->foldername, '.phar')
        );

        $signature = $phar->getSignature();

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'X-License-Key' => $plugin->license,
                'X-Instance-Id' => $instanceId,
                'X-User-Count' => $numberOfUsers,
                'X-Phar-Hash' => $signature,
                'X-Leantime-Version' => $this->appSettings->appVersion,
            ])->get("{$this->marketplaceUrl}/ltmp-api/deactivate/{$plugin->getIdentifier()}");

        } catch (\Exception $e) {
            Log::error($e);
        }

        $jsonResult = [];

        try {
            $body = $response->getBody()->getContents();
            $jsonResult = json_decode($body, true);
        } catch (Exception $e) {
            Log::error($e);
        }

        if ($response->ok() && $jsonResult['valid'] === false) {
            Log::warning($jsonResult['error']);

            return false;
        }

        return true;
    }

    /**
     * Clears cached data related to installation, sessions, and predefined file paths.
     *
     * Removes cached domain events, commands, and enabled plugins from the installation cache store.
     * Clears specific session variables related to template paths and composers.
     * Deletes stored cached files for view paths and composer paths in the framework's storage.
     *
     * @return void
     */
    public function clearCache()
    {
        Cache::store('installation')->forget('domainEvents');
        Cache::store('installation')->forget('commands');
        Cache::store('installation')->forget('plugins.enabledPlugins');

        session()->forget('template_paths');
        session()->forget('composers');

        $files = app()->make(\Illuminate\Filesystem\Filesystem::class);
        $viewPathCachePath = storage_path('framework/viewPaths.php');
        $files->delete($viewPathCachePath);

        $composerPathCachePath = storage_path('framework/composerPaths.php');
        $files->delete($composerPathCachePath);
    }
}
