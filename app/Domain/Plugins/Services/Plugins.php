<?php

namespace Leantime\Domain\Plugins\Services;

use Exception;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Plugins\PluginCompatibility;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Notifications\Services\Notifications;
use Leantime\Domain\Plugins\Events\PluginDisabled;
use Leantime\Domain\Plugins\Events\PluginEnabled;
use Leantime\Domain\Plugins\Events\PluginInstalled;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Permissions\PluginsPermissions;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Users\Services\Users as UsersService;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @api
 */
class Plugins
{
    use DispatchesEvents;

    /**
     * Properties accepted from the request when building a MarketplacePlugin for install.
     * A positive allowlist (fail-closed): the security-sensitive control fields `type` and
     * `marketplaceUrl` are deliberately absent so they can't be mass-assigned from client input,
     * and `version` is intentionally excluded because the caller handles it separately. Everything
     * here is benign marketplace descriptor/display data carried by the install round-trip.
     */
    private const MARKETPLACE_INSTALL_PROPS = [
        'identifier',
        'license',
        'name',
        'excerpt',
        'description',
        'imageUrl',
        'icon',
        'vendorDisplayName',
        'vendorId',
        'marketplaceId',
        'compatibility',
        'categories',
        'tags',
        'rating',
        'reviewCount',
    ];

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

    private int $timeout = 60;

    /** Redirect hops followed when downloading a marketplace archive. */
    private const MAX_DOWNLOAD_REDIRECTS = 5;

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
        private AppSettings $appSettings,
        private PluginArchive $pluginArchive,
        private PluginCompatibility $compatibility,
    ) {
        $this->marketplaceUrl = rtrim($config->marketplaceUrl, '/');
        // $this->marketplaceUrl = 'https://marketplace.leantime.test';
    }

    /**
     * HTTP client for marketplace calls that decide what code gets installed (update checks):
     * certificate-verified, unlike {@see httpClient()} used for browsing.
     */
    protected function verifiedHttpClient()
    {
        return Http::timeout($this->timeout);
    }

    protected function httpClient()
    {

        return Http::withoutVerifying()->timeout($this->timeout);

    }

    /**
     * Retrieves all plugins, optionally filtering only the enabled ones.
     *
     * @param  bool  $enabledOnly  If set to true, only enabled plugins will be returned.
     * @return false|array<InstalledPlugin> Returns an array of all plugins or false if an error occurs.
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function getAllPlugins(bool $enabledOnly = false): false|array
    {
        try {
            $installedPlugins = $this->pluginRepository->getAllPlugins($enabledOnly);
        } catch (\Exception $e) {
            $installedPlugins = [];
        }

        return $this->buildPluginList($installedPlugins, $enabledOnly);
    }

    /**
     * Decorates installed plugins with marketplace data and adds the system plugins from config.
     *
     * @param  array<InstalledPlugin>  $installedPlugins  Plugins read from the database.
     * @param  bool  $enabledOnly  Whether only enabled plugins were requested (passed to the filter).
     * @return array<InstalledPlugin> Plugins keyed by folder name.
     */
    private function buildPluginList(array $installedPlugins, bool $enabledOnly): array
    {
        $installedPluginsById = [];
        $availableUpdates = $this->getAvailableUpdates();

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
            $this->applyCompatibility($plugin);
            $update = $availableUpdates[$plugin->foldername] ?? null;
            if (is_string($update) && version_compare($update, (string) $plugin->version, '>')) {
                $plugin->availableUpdate = $update;
            }
            $installedPluginsById[$plugin->foldername] = $plugin;

        }

        // Gets plugins from the config, which are automatically enabled
        if (isset($this->config->plugins)) {
            $configplugins = explode(',', $this->config->plugins);
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
        $allPlugins = self::dispatch_filter('beforeReturnAllPlugins', $installedPluginsById, ['enabledOnly' => $enabledOnly], 'getAllPlugins');

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
     * Returns the enabled plugins (database plus system plugins from config), cached.
     *
     * Only plugins that support this Leantime version are returned: everything that loads plugin
     * code (register files, routes, views, the console) goes through here, so an incompatible
     * plugin stays enabled in the database but is not loaded. Administrators are told once.
     *
     * A failing plugin-table query is never cached as "no plugins": the next call retries. Web
     * requests keep running without user plugins in that case; callers that must not silently run
     * without them (the console scheduler) pass $failOnDatabaseError to get the exception instead.
     *
     * @param  bool  $failOnDatabaseError  Rethrow a failing plugin-table query instead of falling back.
     * @return array|false|mixed
     *
     * @throws BindingResolutionException
     * @throws \Exception When the plugin table cannot be read and $failOnDatabaseError is true.
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function getEnabledPlugins(bool $failOnDatabaseError = false): mixed
    {

        // Keyed by the Leantime version: after a core update the list is rebuilt, so plugins that
        // don't support the new version drop out on the first request.
        $cacheKey = $this->enabledPluginsCacheKey();

        if (Cache::store('installation')->has($cacheKey) && $this->config->debug === false) {
            $enabledPlugins = static::dispatch_filter('beforeReturnCachedPlugins', Cache::store('installation')->get($cacheKey), ['enabledOnly' => true]);

            return $enabledPlugins;
        }

        try {
            $installedPlugins = $this->pluginRepository->getAllPlugins(true);
        } catch (\Exception $e) {
            if ($failOnDatabaseError) {
                throw $e;
            }

            Log::error('Could not read enabled plugins, continuing without user plugins: '.$e->getMessage());

            return self::dispatch_filter(
                hook: 'beforeReturnCachedPlugins',
                payload: $this->buildPluginList([], true),
                available_params: ['enabledOnly' => true]);
        }

        Cache::store('installation')->set($cacheKey, $this->withoutIncompatible($this->buildPluginList($installedPlugins, true)));

        /**
         * Filters session array of enabled plugins before returning
         */
        return self::dispatch_filter(
            hook: 'beforeReturnCachedPlugins',
            payload: Cache::store('installation')->get($cacheKey),
            available_params: ['enabledOnly' => true]);

    }

    /**
     * @return InstalledPlugin[]
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
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
                    Log::warning("Can't create plugin from composer");
                    Log::warning($e);

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

        return $this->pluginFromComposer($pluginFile, $pluginFolder, $license_key, $format);
    }

    /**
     * Builds an installed-plugin model from a plugin's decoded composer.json.
     *
     * @param  array<string, mixed>  $pluginFile  The decoded composer.json.
     * @param  string  $pluginFolder  The plugin's folder name.
     * @param  string  $license_key  The marketplace license key, if any.
     * @param  string  $format  'folder' or 'phar'.
     */
    private function pluginFromComposer(array $pluginFile, string $pluginFolder, string $license_key, string $format): InstalledPlugin
    {
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

        $plugin->requires = $this->compatibility->rangeFor($pluginFile);
        $plugin->compatible = $this->compatibility->isCompatible($plugin->requires);

        return $plugin;
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
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

        $added = $this->pluginRepository->addPlugin($plugin);

        if ($added !== false) {
            PluginInstalled::dispatch(plugin: (string) $plugin->foldername, format: (string) $plugin->format);
        }

        return $added;
    }

    /**
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function enablePlugin(int $id): bool
    {
        return $this->enable($id, null);
    }

    /**
     * Enables a plugin after a core update, checking it against the version just installed.
     *
     * The updater still runs the previous release's code, so the running version is the old one;
     * a plugin that supports only the new release would otherwise be refused. Not @api: only
     * system:update calls it.
     *
     * @param  int  $id  The plugin id.
     * @param  string  $installedLeantimeVersion  The Leantime version that was just installed.
     */
    public function enablePluginForVersion(int $id, string $installedLeantimeVersion): bool
    {
        return $this->enable($id, $installedLeantimeVersion);
    }

    /**
     * Enables a plugin when it supports $leantimeVersion (default: the running version) and, for
     * marketplace plugins, its license is valid.
     */
    private function enable(int $id, ?string $leantimeVersion): bool
    {
        $this->clearCache();

        $pluginModel = $this->pluginRepository->getPlugin($id);

        $this->applyCompatibility($pluginModel, $leantimeVersion);
        if ($pluginModel->compatible === false) {
            Log::warning('Not enabling plugin '.$pluginModel->foldername.': it supports Leantime '.$pluginModel->requires.', not '.($leantimeVersion ?? $this->appSettings->appVersion));

            return false;
        }

        if ($pluginModel->format === 'phar' && ! $this->validLicense($pluginModel)) {
            return false;
        }

        $enabled = $this->pluginRepository->enablePlugin($id);

        if ($enabled) {
            PluginEnabled::dispatch(plugin: (string) $pluginModel->foldername);
        }

        return $enabled;

    }

    /**
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function disablePlugin(int $id): bool
    {
        $this->clearCache();

        $pluginModel = $this->pluginRepository->getPlugin($id);

        $result = $this->pluginRepository->disablePlugin($id);

        if ($pluginModel->format === 'phar') {

            $this->deactivate($pluginModel);

        }

        if ($result) {
            PluginDisabled::dispatch(plugin: (string) $pluginModel->foldername);
        }

        return $result;
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function removePlugin(int $id): bool
    {
        $this->clearCache();

        /** @var InstalledPlugin|false $plugin */
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
        $plugins = $this->httpClient()->get(
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

                    $plugins[$category['slug']]['plugins'][Str::lower($plugin['identifier'])] = self::buildMarketplacePluginFromListData($plugin, $priceString);

                    $pluginsFlat[Str::lower($plugin['identifier'])] = $plugins[$category['slug']]['plugins'][Str::lower($plugin['identifier'])];
                }
            }
        }

        Cache::store('installation')->set('plugins.marketplacePlugins', $plugins);
        Cache::store('installation')->set('plugins.marketplacePluginsFlat', $pluginsFlat);

        return $plugins;
    }

    public function getLatestPluginUpdates(int $page, string $query = ''): array
    {
        $plugins = $this->httpClient()->get(
            "{$this->marketplaceUrl}/ltmp-api"
            .(! empty($query) ? "/search/$query" : '/index')
            ."/$page".'?lt-v='.$this->appSettings->appVersion
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

                    $plugins[$category['slug']]['plugins'][Str::lower($plugin['identifier'])] = self::buildMarketplacePluginFromListData($plugin, $priceString);

                    $pluginsFlat[Str::lower($plugin['identifier'])] = $plugins[$category['slug']]['plugins'][Str::lower($plugin['identifier'])];
                }
            }
        }

        Cache::store('installation')->set('plugins.marketplacePlugins', $plugins);
        Cache::store('installation')->set('plugins.marketplacePluginsFlat', $pluginsFlat);

        return $pluginsFlat;
    }

    /**
     * Builds a MarketplacePlugin from listing API data with proper type coercion.
     *
     * The marketplace API may return unexpected types (string instead of array,
     * null instead of int, etc.) so all values are explicitly cast to match the
     * MarketplacePlugin model's typed properties.
     *
     * @param  array  $plugin  Raw plugin data from the marketplace listing API.
     * @param  string  $priceString  Pre-formatted monthly price string.
     * @return MarketplacePlugin The hydrated plugin model.
     */
    private static function buildMarketplacePluginFromListData(array $plugin, string $priceString): MarketplacePlugin
    {
        return build(new MarketplacePlugin)
            ->set('identifier', (string) ($plugin['identifier'] ?? ''))
            ->set('name', (string) ($plugin['post_title'] ?? ''))
            ->set('excerpt', (string) ($plugin['excerpt'] ?? ''))
            ->set('description', '')
            ->set('imageUrl', (string) ($plugin['icon'] ?? ''))
            ->set('icon', (string) ($plugin['icon'] ?? ''))
            ->set('vendorDisplayName', (string) ($plugin['vendor'] ?? ''))
            ->set('vendorId', (int) ($plugin['vendor_id'] ?? 0))
            ->set('vendorEmail', (string) ($plugin['vendor_email'] ?? ''))
            ->set('marketplaceUrl', '')
            ->set('startingPrice', '$'.($plugin['price'] ?? ''))
            ->set('calculatedMonthlyPrice', $priceString)
            ->set('rating', (string) ($plugin['rating'] ?? ''))
            ->set('reviewCount', (int) ($plugin['review_count'] ?? 0))
            ->set('reviews', is_array($plugin['reviews'] ?? null) ? $plugin['reviews'] : [])
            ->set('marketplaceId', (string) ($plugin['product_id'] ?? ''))
            ->set('version', (string) ($plugin['version'] ?? ''))
            ->set('pricingTiers', is_array($plugin['tiers'] ?? null) ? $plugin['tiers'] : [])
            ->set('categories', is_array($plugin['categories'] ?? null) ? $plugin['categories'] : [])
            ->set('tags', is_array($plugin['tags'] ?? null) ? $plugin['tags'] : [])
            ->set('compatibility', is_array($plugin['compatibility'] ?? null) ? $plugin['compatibility'] : [])
            ->get();
    }

    /**
     * Builds a MarketplacePlugin from raw request properties.
     *
     * Request data arrives with every field encoded as a string. Any field whose
     * value is a valid JSON string (arrays/objects) is decoded back into its native
     * structure before being assigned to the model. The 'version' field is treated
     * separately by the caller and is therefore expected to already be removed.
     *
     * @param  array<string, mixed>  $pluginProps  Raw plugin properties from the request (without 'version').
     * @return MarketplacePlugin The hydrated plugin model.
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function buildMarketplacePluginFromRequest(array $pluginProps): MarketplacePlugin
    {
        $builder = build(new MarketplacePlugin);

        foreach ($pluginProps as $key => $value) {
            // Only accept the properties the install round-trip legitimately carries. Everything
            // else — notably control fields like `type` and `marketplaceUrl` — is ignored, so a
            // request can't mass-assign arbitrary model state (the download always targets the
            // server-configured marketplace, keyed by identifier).
            if (! in_array($key, self::MARKETPLACE_INSTALL_PROPS, true)) {
                continue;
            }

            if (is_string($value)) {
                $newValue = json_decode(json: $value, flags: JSON_OBJECT_AS_ARRAY);

                if (json_last_error() === JSON_ERROR_NONE && $newValue !== null) {
                    $value = $newValue;
                }
            }

            $builder->set($key, $value);
        }

        return $builder->get();
    }

    /**
     * Determines whether a marketplace plugin belongs to the "bundles" category.
     *
     * @param  MarketplacePlugin  $plugin  The plugin to inspect.
     * @return bool True if the plugin is part of the bundles category.
     *
     * @api
     */
    public function isBundle(MarketplacePlugin $plugin): bool
    {
        return collect($plugin->categories)->where('slug', '=', 'bundles')->count() > 0;
    }

    /**
     * Parses a marketplace download/install RequestException into a clean,
     * user-facing error message.
     *
     * The marketplace responds with status-code prefixes (e.g.
     * "HTTP request returned status code 500:") wrapped around a JSON body. This
     * strips those prefixes, decodes the JSON and returns the most specific
     * message available, falling back to a generic installation error.
     *
     * @param  RequestException  $exception  The exception thrown while installing.
     * @return string A clean error message safe to show to the user.
     *
     * @api
     */
    public function parseMarketplaceError(RequestException $exception): string
    {
        // Any status: the marketplace also answers 409 for a release that doesn't support this version.
        $errorJson = (string) preg_replace('/^HTTP request returned status code \d+:\s*/', '', $exception->getMessage());
        $errors = json_decode(trim($errorJson));

        return $errors->error ?? $errors->message ?? 'There was an error installing the plugin';
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
        $response = $this->httpClient()->get("$this->marketplaceUrl/ltmp-api/details/$identifier?lt-v=".$this->appSettings->appVersion);

        if (! $response->ok()) {
            return false;
        }

        $data = $response->json();

        return build(new MarketplacePlugin)
            ->set('identifier', $identifier)
            ->set('name', (string) ($data['name'] ?? ''))
            ->set('icon', (string) ($data['icon'] ?? ''))
            ->set('excerpt', (string) ($data['excerpt'] ?? ''))
            ->set('imageUrl', (string) ($data['icon'] ?? ''))
            ->set('description', nl2br((string) ($data['description'] ?? '')))
            ->set('marketplaceUrl', (string) ($data['marketplaceUrl'] ?? ''))
            ->set('vendorId', (int) ($data['vendor']['id'] ?? 0))
            ->set('vendorDisplayName', (string) ($data['vendor']['name'] ?? ''))
            ->set('vendorEmail', (string) ($data['vendor']['email'] ?? ''))
            ->set('rating', (string) ($data['reviews']['average'] ?? 'N/A'))
            ->set('reviewCount', (int) ($data['reviews']['count'] ?? 0))
            ->set('reviews', is_array($data['reviews']['list'] ?? null) ? $data['reviews']['list'] : [])
            ->set('marketplaceId', (string) ($data['productId'] ?? ''))
            ->set('version', (string) ($data['version'] ?? ''))
            ->set('pricingTiers', is_array($data['tiers'] ?? null) ? $data['tiers'] : [])
            ->set('categories', is_array($data['categories'] ?? null) ? $data['categories'] : [])
            ->set('tags', is_array($data['tags'] ?? null) ? $data['tags'] : [])
            ->set('compatibility', is_array($data['compatibility'] ?? null) ? $data['compatibility'] : [])
            ->get();
    }

    /**
     * Installs a marketplace plugin by downloading, extracting, and registering it in the plugin repository.
     *
     * The archive is downloaded over verified TLS and checked by {@see PluginArchive} (safe entries,
     * then the phar signature in a staging directory) before it replaces the installed version.
     *
     * @param  MarketplacePlugin  $plugin  The marketplace plugin to be installed, including its identifier and license key.
     * @param  string  $version  The version of the plugin to be installed.
     * @param  string|null  $expectedFolder  For an update: the installed plugin's folder. A download
     *                                       for any other folder is refused before files change.
     *
     * @throws RequestException If the HTTP request to download the plugin fails or returns an unexpected response type.
     * @throws \Exception If the plugin cannot be downloaded, removed, or added to the system.
     * @throws \RuntimeException If the plugin directory cannot be created.
     */
    public function installMarketplacePlugin(MarketplacePlugin $plugin, string $version, ?string $expectedFolder = null): void
    {
        // Two installs of the same plugin at once would share the kept previous version (and the
        // second could restore the first one's files): one at a time per plugin.
        $lock = Cache::lock('plugins.install.'.strtolower((string) $plugin->identifier), 900);
        if (! $lock->get()) {
            throw new \RuntimeException(__('notification.plugin_update_busy'));
        }

        try {
            $this->installMarketplacePluginLocked($plugin, $version, $expectedFolder);
        } finally {
            $lock->release();
        }
    }

    /**
     * {@see installMarketplacePlugin()} under its per-plugin lock.
     */
    private function installMarketplacePluginLocked(MarketplacePlugin $plugin, string $version, ?string $expectedFolder): void
    {

        $this->clearCache();

        // An update replaces files that must be there to fall back to. Without them it would run as
        // a fresh install (no load check, no rollback), so it is refused before anything is fetched.
        if ($expectedFolder !== null && ! is_dir($this->pluginDirectory.$expectedFolder)) {
            throw new \RuntimeException(sprintf(__('notification.plugin_update_files_missing'), $expectedFolder));
        }

        // Decided from an earlier snapshot (the scheduler, or a page opened a while ago): with the
        // lock held, the target must still be newer than what is installed now. Never a reinstall
        // or a downgrade after someone else updated in between.
        $currentVersion = $expectedFolder !== null ? $this->installedPluginFor($expectedFolder)?->version : null;
        if ($currentVersion !== null && version_compare(ltrim($version, 'v'), ltrim((string) $currentVersion, 'v'), '<=')) {
            throw new \RuntimeException(sprintf(__('notification.plugin_update_not_newer'), $expectedFolder, $currentVersion, $version));
        }

        // Updating an installed plugin: no need to type the license key again.
        if (empty($plugin->license)) {
            $plugin->license = $this->findInstalledMarketplacePlugin((string) $plugin->identifier)?->license;
        }

        // The archive is code this server will run, so it is only ever fetched over verified
        // HTTPS (unlike the other marketplace calls made through httpClient()): the marketplace
        // URL must be https and redirects may not leave https (downloadMarketplaceArchive()).
        if (strtolower((string) parse_url($this->marketplaceUrl, PHP_URL_SCHEME)) !== 'https') {
            throw new \Exception(__('notification.plugin_cant_download'));
        }

        $response = $this->downloadMarketplaceArchive(
            "{$this->marketplaceUrl}/ltmp-api/download/{$plugin->identifier}/{$version}",
            [
                'X-License-Key' => $plugin->license,
                'X-Instance-Id' => $this->settingsService->getCompanyId(),
                'X-User-Count' => $this->usersService->getNumberOfUsers(activeOnly: true, includeApi: false),
                'X-Leantime-Version' => $this->appSettings->appVersion,
            ]
        );

        if (! $response->ok()) {
            throw new RequestException($response);
        }

        if ($response->header('Content-Type') !== 'application/zip') {
            throw new RequestException($response);
        }

        $filename = $response->header('Content-Disposition');
        $filename = trim(substr($filename, strpos($filename, 'filename=') + 9), " \"'");
        $foldername = Str::studly(basename($filename, '.zip'));

        // The folder name ends up in filesystem paths; only accept a plain identifier.
        if (preg_match('/^[A-Za-z0-9_-]+$/', $foldername) !== 1) {
            throw new \Exception(__('notification.plugin_cant_download'));
        }

        // An update must land in the plugin being updated, never in another plugin's folder.
        if ($expectedFolder !== null && strcasecmp($foldername, $expectedFolder) !== 0) {
            throw new \RuntimeException(sprintf(__('notification.plugin_update_wrong_package'), $expectedFolder, $foldername));
        }

        // A private, unpredictable temp path for the download, always removed afterwards.
        $temporaryFile = Str::finish(sys_get_temp_dir(), '/').'leantime-plugin-'.bin2hex(random_bytes(16)).'.zip';
        $pluginDir = "{$this->pluginDirectory}{$foldername}";
        $isUpdate = is_dir($pluginDir);
        $previousCopy = $isUpdate ? $this->previousVersionPath($foldername) : null;
        $installedName = $isUpdate ? $this->installedNameFor($foldername) : null;

        try {
            if (! file_put_contents($temporaryFile, $response->body())) {
                throw new \Exception(__('notification.plugin_cant_download'));
            }

            // Verifies the archive (entries, then the phar signature in a staging directory) before
            // anything reaches the plugin directory; the installed version is only replaced after.
            // An update keeps the version it replaces, so a broken one can be rolled back.
            $this->pluginArchive->install($temporaryFile, $foldername, $pluginDir, $previousCopy);
        } finally {
            if (is_file($temporaryFile)) {
                @unlink($temporaryFile);
            }
        }

        // An update must load before it counts; otherwise the previous version comes back. The
        // fresh process also reads the new composer.json, which this process can't: it still
        // holds the previous phar.
        $composer = $isUpdate ? $this->verifyUpdateOrRollBack($foldername, (string) $previousCopy, $version, $installedName) : null;

        try {
            // A new install has no previous phar in this process, so it can read composer.json itself.
            $pluginModel = $composer !== null
                ? $this->pluginFromComposer($composer, $foldername, (string) $plugin->license, 'phar')
                : $this->createPluginFromComposer($foldername, (string) $plugin->license);

            $this->recordInstalledPlugin($pluginModel);
        } catch (\Throwable $e) {
            // The files changed but the database didn't: an update goes back to the previous
            // version rather than leaving the two out of step.
            if ($isUpdate) {
                Log::error('Plugin '.$foldername.' update could not be recorded, restoring the previous version: '.$e->getMessage());
                $this->pluginArchive->restore((string) $previousCopy, $pluginDir);
                $this->clearCache();

                throw new \RuntimeException(sprintf(__('notification.plugin_update_rolled_back'), $foldername, Str::limit($e->getMessage(), 300)), 0, $e);
            }

            throw $e;
        }

        $this->forgetAvailableUpdate($foldername);
        $this->clearCache();
    }

    /**
     * Saves a just-installed plugin: one row per plugin. An update rewrites the existing row
     * (keeping it enabled and, when no new key was given, its license) instead of adding a
     * second, disabled one; extra rows left by earlier updates are removed.
     *
     * @throws \Exception When a new plugin can't be added.
     */
    private function recordInstalledPlugin(InstalledPlugin $pluginModel): void
    {
        $existingIds = $this->pluginRepository->getPluginIdsByFolder((string) $pluginModel->foldername);

        if ($existingIds === []) {
            if (! $this->pluginRepository->addPlugin($pluginModel)) {
                throw new \Exception(__('notification_cant_add_to_db'));
            }

            PluginInstalled::dispatch(plugin: (string) $pluginModel->foldername, format: (string) $pluginModel->format);

            return;
        }

        $this->pluginRepository->replacePluginRows(array_shift($existingIds), $pluginModel, $existingIds);
    }

    /**
     * Updates an installed marketplace plugin to its newest version that supports this Leantime
     * version, with the license key it was installed with. A version that doesn't load is rolled
     * back (the error says so).
     *
     * @param  int  $id  The plugin id.
     * @return bool False when there is nothing to update to.
     *
     * @throws \Exception When the download, install or verification fails (rolled back where possible).
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function updatePlugin(int $id): bool
    {
        $installed = $this->pluginRepository->getPlugin($id);

        if ($installed === false || $installed->format !== $this->pluginFormat['phar']) {
            return false;
        }

        $version = $this->findUpdate($installed);
        if ($version === null) {
            return false;
        }

        $this->updatePluginTo($installed, $version);

        return true;
    }

    /**
     * Updates an installed marketplace plugin to exactly $version (with its stored license key).
     * The install verifies that this version is what got installed, else it rolls back.
     * Protected (never reachable over JSON-RPC) only so tests can stand in for the install.
     *
     * @throws \Exception When the download, install or verification fails (rolled back where possible).
     */
    protected function updatePluginTo(InstalledPlugin $installed, string $version): void
    {
        $marketplacePlugin = build(new MarketplacePlugin)
            ->set('identifier', $installed->getIdentifier())
            ->set('license', (string) $installed->license)
            ->get();

        $this->installMarketplacePlugin($marketplacePlugin, $version, (string) $installed->foldername);
    }

    /**
     * Asks the marketplace for a newer version of every installed marketplace plugin, keeps the
     * result for My Apps, and tells the administrators about each new version once.
     *
     * The marketplace lists only versions whose range includes this Leantime version (it gets
     * `lt-v`); the range is checked here again so a listing mistake can't offer an incompatible one.
     * A plugin the marketplace can't answer for (outage, error) is not reported as up to date: it
     * keeps what the previous check found and is listed in `failed` (logged).
     *
     * @return array{updates: array<string, string>, failed: array<int, string>}
     *                                                                           updates: plugin folder => version; failed: plugin folders that could not be checked.
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function checkForUpdates(): array
    {
        $updates = [];
        $failed = [];
        $previousUpdates = $this->getAvailableUpdates();

        $marketplacePlugins = collect($this->pluginRepository->getAllPlugins(false) ?: [])
            ->filter(fn (InstalledPlugin $plugin) => $plugin->format === $this->pluginFormat['phar']);

        // One request for all plugins when the marketplace offers it; otherwise one per plugin.
        $releasesByIdentifier = $marketplacePlugins->isEmpty()
            ? []
            : $this->fetchLatestReleases($marketplacePlugins->map(fn (InstalledPlugin $plugin) => $plugin->getIdentifier())->values()->all());

        foreach ($marketplacePlugins as $plugin) {
            try {
                // A plugin the batch answer left out is asked about on its own (never "up to date").
                $version = $this->findUpdate($plugin, $releasesByIdentifier[strtolower($plugin->getIdentifier())] ?? null);
            } catch (\Throwable $e) {
                Log::warning('Could not check for an update of plugin '.$plugin->foldername.': '.$e->getMessage());
                $failed[] = (string) $plugin->foldername;
                if (isset($previousUpdates[$plugin->foldername])) {
                    $updates[$plugin->foldername] = $previousUpdates[$plugin->foldername];
                }

                continue;
            }

            if ($version === null) {
                continue;
            }

            $updates[$plugin->foldername] = $version;
            $this->notifyUpdateOnce($plugin, $version);
        }

        $this->settingsService->saveSetting('plugins.availableUpdates', json_encode($updates));
        $this->settingsService->saveSetting('plugins.updatesCheckedAt', dtHelper()->dbNow()->formatDateTimeForDb());
        $this->clearCache();

        return ['updates' => $updates, 'failed' => $failed];
    }

    /**
     * The releases of several marketplace plugins in one request (`ltmp-api/latest`), keyed by
     * lower-cased identifier, each in the shape of a plugin's compatibility list. Null when the
     * marketplace doesn't offer the batch call (older marketplace) or it fails: the caller then
     * asks per plugin.
     *
     * @param  array<int, string>  $identifiers  Marketplace identifiers of the installed plugins.
     * @return array<string, array<int, array<string, mixed>>>|null
     */
    private function fetchLatestReleases(array $identifiers): ?array
    {
        try {
            $response = $this->verifiedHttpClient()->get("{$this->marketplaceUrl}/ltmp-api/latest", [
                'identifiers' => implode(',', $identifiers),
                'lt-v' => $this->appSettings->appVersion,
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        $releases = $response->ok() ? $response->json('plugins') : null;

        if (! is_array($releases)) {
            return null;
        }

        return collect($releases)
            ->filter(fn ($pluginReleases) => is_array($pluginReleases))
            ->mapWithKeys(fn ($pluginReleases, $identifier) => [strtolower((string) $identifier) => array_values($pluginReleases)])
            ->all();
    }

    /**
     * Turns automatic plugin updates on or off (off by default). When on, the daily update check
     * installs new minor and patch releases of marketplace plugins; major releases wait for an
     * administrator.
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function setAutoUpdate(bool $enabled): void
    {
        $this->settingsService->saveSetting('plugins.autoUpdate', $enabled ? '1' : '0');
    }

    /**
     * Whether automatic plugin updates are on (off unless an administrator turned them on).
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function isAutoUpdateEnabled(): bool
    {
        return (string) $this->settingsService->getSetting('plugins.autoUpdate', '0') === '1';
    }

    /**
     * The daily plugin update job: checks for updates and, when automatic updates are on, installs
     * the minor and patch ones. Each automatic update (or its rollback) is reported to the admins.
     *
     * Not @api: the scheduler calls it.
     *
     * Result (all keyed by plugin folder): available = folder => version found; checkFailed = folders
     * the marketplace didn't answer for (their earlier result is kept); updated = folder => version
     * installed; failed = folder => reason; waiting = folder => major version left for an admin.
     *
     * @return array{available: array<string, string>, checkFailed: array<int, string>, updated: array<string, string>, failed: array<string, string>, waiting: array<string, string>}
     */
    public function runScheduledUpdates(): array
    {
        $check = $this->checkForUpdates();
        $result = ['available' => $check['updates'], 'checkFailed' => $check['failed'], 'updated' => [], 'failed' => [], 'waiting' => []];

        if ($result['available'] === [] || ! $this->isAutoUpdateEnabled()) {
            return $result;
        }

        $installedByFolder = collect($this->pluginRepository->getAllPlugins(false) ?: [])->keyBy('foldername');

        foreach ($result['available'] as $folder => $version) {
            $plugin = $installedByFolder->get($folder);
            if ($plugin === null) {
                continue;
            }

            if (! self::isMinorOrPatchUpdate((string) $plugin->version, $version)) {
                $result['waiting'][$folder] = $version;

                continue;
            }

            try {
                // Exactly the version that passed the minor/patch check, never a newer one the
                // marketplace may list by now.
                $this->updatePluginTo($plugin, $version);
                $result['updated'][$folder] = $version;
                $message = sprintf(__('notification.plugin_auto_updated'), $plugin->name, $version);
            } catch (\Throwable $e) {
                $result['failed'][$folder] = $e->getMessage();
                $message = sprintf(__('notification.plugin_auto_update_failed'), $plugin->name, $version, Str::limit($e->getMessage(), 300));
                Log::warning('Automatic update of plugin '.$folder.' to '.$version.' failed: '.$e->getMessage());
            }

            try {
                $this->notifyAdmins($plugin->id, 'plugin_update', $message);
            } catch (\Throwable $e) {
                Log::warning('Could not notify about the automatic update of plugin '.$folder.': '.$e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Whether $to is a minor or patch release after $from (same major version), which automatic
     * updates may install.
     */
    public static function isMinorOrPatchUpdate(string $from, string $to): bool
    {
        $fromMajor = (int) explode('.', ltrim($from, 'v'))[0];
        $toMajor = (int) explode('.', ltrim($to, 'v'))[0];

        return $fromMajor === $toMajor && version_compare(ltrim($to, 'v'), ltrim($from, 'v'), '>');
    }

    /**
     * The updates found by the last {@see checkForUpdates()}: plugin folder => version.
     *
     * @return array<string, string>
     */
    private function getAvailableUpdates(): array
    {
        // Also read on the plugin list's database-error path: no update badges then, never an error.
        try {
            $stored = json_decode((string) $this->settingsService->getSetting('plugins.availableUpdates', '[]'), true);
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($stored) ? array_filter($stored, 'is_string') : [];
    }

    /**
     * The newest marketplace version of a plugin that supports this Leantime version and is newer
     * than the installed one, or null when there is none.
     *
     * @param  array<int, array<string, mixed>>|null  $releases  The plugin's releases, when already
     *                                                           fetched in a batch; null asks the marketplace.
     *
     * @throws \RuntimeException When the marketplace doesn't answer for the plugin (never "no update").
     */
    private function findUpdate(InstalledPlugin $plugin, ?array $releases = null): ?string
    {
        if ($releases === null) {
            // Verified TLS: this answer picks the version that gets installed.
            $response = $this->verifiedHttpClient()->get("{$this->marketplaceUrl}/ltmp-api/details/".rawurlencode($plugin->getIdentifier()), [
                'lt-v' => $this->appSettings->appVersion,
            ]);
            $releases = $response->ok() ? $response->json('compatibility') : null;

            if (! is_array($releases)) {
                throw new \RuntimeException('The marketplace did not answer for '.$plugin->getIdentifier());
            }
        }

        $latest = collect($releases)
            ->filter(fn ($release) => is_array($release) && ! empty($release['version_number']))
            ->filter(fn ($release) => $this->compatibility->isCompatible($this->releaseRange($release)))
            ->map(fn ($release) => ltrim((string) $release['version_number'], 'v'))
            ->sort(fn ($a, $b) => version_compare($b, $a))
            ->first();

        return $latest !== null && version_compare($latest, ltrim((string) $plugin->version, 'v'), '>') ? $latest : null;
    }

    /**
     * The Leantime range of one marketplace release ("supported from / to"; "latest", "dev" or
     * "master" as "to" means no upper bound).
     *
     * @param  array<string, mixed>  $release  One entry of a marketplace plugin's compatibility list.
     */
    private function releaseRange(array $release): string
    {
        $from = ltrim((string) ($release['supported_version_from'] ?? '0'), 'v');
        $to = strtolower(ltrim((string) ($release['supported_version_to'] ?? ''), 'v'));

        if ($to === '' || in_array($to, ['latest', 'dev', 'master'], true)) {
            return '>='.$from;
        }

        return '>='.$from.' <='.$to;
    }

    /**
     * Runs `plugin:verify` for an updated plugin in a fresh process. When the new version doesn't
     * load or doesn't support this Leantime version, the previous version is put back and the
     * update fails with the reason.
     *
     * Without a PHP CLI, a timeout, or no readable result, the update is rolled back too: it is
     * only kept once it has been seen to load (and this process can't read the new phar's
     * composer.json, so the database would otherwise record the old version).
     *
     * The installed release must also be the one requested: the same package name as before and
     * the requested version.
     *
     * @param  string  $requestedVersion  The version that was asked for.
     * @param  string|null  $installedName  The package name of the version being replaced.
     * @return array<string, mixed> The new version's composer.json.
     *
     * @throws \RuntimeException When the update was rolled back.
     */
    private function verifyUpdateOrRollBack(string $folder, string $previousCopy, string $requestedVersion, ?string $installedName): array
    {
        [$composer, $problem] = $this->runPluginVerify($folder);

        // What was installed must be what was asked for: the same package, the requested release.
        $installedVersion = ltrim((string) ($composer['version'] ?? ''), 'v');
        $problem ??= match (true) {
            $installedVersion !== ltrim($requestedVersion, 'v') => 'the download was version '.($installedVersion ?: 'unknown').', not '.$requestedVersion,
            $installedName !== null && strcasecmp((string) ($composer['name'] ?? ''), $installedName) !== 0 => 'the download was the package '.($composer['name'] ?? 'unknown').', not '.$installedName,
            ! $this->compatibility->isCompatible($this->compatibility->rangeFor($composer)) => 'it does not support Leantime '.$this->appSettings->appVersion,
            default => null,
        };

        if ($problem === null) {
            return $composer;
        }

        Log::error('Plugin '.$folder.' update failed its load check, restoring the previous version: '.$problem);
        $this->pluginArchive->restore($previousCopy, "{$this->pluginDirectory}{$folder}");
        $this->clearCache();

        throw new \RuntimeException(sprintf(__('notification.plugin_update_rolled_back'), $folder, Str::limit($problem, 300)));
    }

    /**
     * Runs `plugin:verify {folder} --json` in a fresh PHP process.
     *
     * Protected (not public, so never reachable over JSON-RPC) only so tests can stand in for the
     * fresh process.
     *
     * @return array{0: array<string, mixed>, 1: string|null} [composer.json of the installed version, problem or null]
     */
    protected function runPluginVerify(string $folder): array
    {
        $php = (new PhpExecutableFinder)->find(false);

        if ($php === false) {
            return [[], 'no PHP command line binary was found to check that it loads'];
        }

        try {
            $process = new Process([$php, APP_ROOT.'/bin/leantime', 'plugin:verify', $folder, '--json'], APP_ROOT, null, null, 120);
            $process->run();
        } catch (\Throwable $e) {
            // Timeout or the process could not start.
            return [[], 'the load check did not complete: '.$e->getMessage()];
        }

        $lines = array_filter(explode("\n", trim($process->getOutput())));
        $result = json_decode((string) end($lines), true);

        if (! $process->isSuccessful() || ! is_array($result) || ($result['ok'] ?? false) !== true) {
            return [[], (string) ($result['error'] ?? trim($process->getErrorOutput().' '.$process->getOutput()))];
        }

        if (! is_array($result['composer'] ?? null)) {
            return [[], 'its composer.json could not be read'];
        }

        return [$result['composer'], null];
    }

    /**
     * Where an update keeps the version it replaced (outside the plugin directory, so the
     * new-plugin scan never mistakes it for a plugin).
     */
    private function previousVersionPath(string $folder): string
    {
        return storage_path('app/plugins-previous/'.$folder);
    }

    /**
     * The package name (composer "name") recorded for an installed plugin folder, if any.
     */
    private function installedNameFor(string $folder): ?string
    {
        $installed = $this->installedPluginFor($folder);

        return $installed !== null && ! empty($installed->name) ? (string) $installed->name : null;
    }

    /**
     * The installed plugin row for a folder, read fresh from the database.
     */
    private function installedPluginFor(string $folder): ?InstalledPlugin
    {
        return collect($this->pluginRepository->getAllPlugins(false) ?: [])
            ->first(fn (InstalledPlugin $plugin) => strcasecmp((string) $plugin->foldername, $folder) === 0);
    }

    /**
     * The installed marketplace plugin with this marketplace identifier, if any.
     */
    private function findInstalledMarketplacePlugin(string $identifier): ?InstalledPlugin
    {
        if ($identifier === '') {
            return null;
        }

        return collect($this->pluginRepository->getAllPlugins(false) ?: [])
            ->first(fn (InstalledPlugin $installed) => $installed->format === $this->pluginFormat['phar']
                && strcasecmp($installed->getIdentifier(), $identifier) === 0);
    }

    /**
     * Drops a plugin from the stored available updates once it has been updated.
     */
    private function forgetAvailableUpdate(string $folder): void
    {
        $updates = $this->getAvailableUpdates();

        if (! isset($updates[$folder])) {
            return;
        }

        unset($updates[$folder]);
        $this->settingsService->saveSetting('plugins.availableUpdates', json_encode($updates));
    }

    /**
     * One admin notification per plugin and new version (saved after it went out, so a failed
     * send is retried by the next check).
     */
    private function notifyUpdateOnce(InstalledPlugin $plugin, string $version): void
    {
        $settingKey = 'plugins.updateNotified.'.$plugin->foldername;

        if (! isset($plugin->id) || $this->settingsService->getSetting($settingKey) === $version) {
            return;
        }

        // The daily job and a manual check can run at once: only the lock holder sends, and it
        // re-reads the marker inside the lock.
        $lock = Cache::lock('plugins.updateNotify.'.$plugin->foldername, 30);
        if (! $lock->get()) {
            return;
        }

        try {
            if ($this->settingsService->getSetting($settingKey) === $version) {
                return;
            }

            $this->notifyAdmins(
                $plugin->id,
                'plugin_update',
                sprintf(__('notification.plugin_update_available'), $plugin->name, $version, $plugin->version)
            );
            $this->settingsService->saveSetting($settingKey, $version);
        } catch (\Throwable $e) {
            Log::warning('Could not notify about the update of plugin '.$plugin->foldername.': '.$e->getMessage());
        } finally {
            $lock->release();
        }
    }

    /**
     * Downloads a marketplace archive over verified HTTPS, following at most
     * MAX_DOWNLOAD_REDIRECTS https redirects itself. The marketplace headers (license key,
     * instance id, user count) are sent only to the configured marketplace origin and are never
     * forwarded to a redirect target on another origin.
     *
     * @param  string  $url  The marketplace download URL.
     * @param  array<string, mixed>  $marketplaceHeaders  Headers meant for the marketplace only.
     *
     * @throws \Exception When a redirect leaves https or the redirect limit is exceeded.
     */
    private function downloadMarketplaceArchive(string $url, array $marketplaceHeaders): Response
    {
        $marketplaceOrigin = self::urlOrigin($this->marketplaceUrl);

        for ($hop = 0; ; $hop++) {
            if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
                throw new \Exception(__('notification.plugin_cant_download'));
            }

            $headers = self::urlOrigin($url) === $marketplaceOrigin ? $marketplaceHeaders : [];

            $response = Http::timeout($this->timeout)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($headers)
                ->get($url);

            if (! $response->redirect()) {
                return $response;
            }

            $location = (string) $response->header('Location');
            if ($location === '' || $hop >= self::MAX_DOWNLOAD_REDIRECTS) {
                throw new \Exception(__('notification.plugin_cant_download'));
            }

            $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));
        }
    }

    /**
     * The origin (scheme, host, effective port) of a URL, lower-cased.
     */
    private static function urlOrigin(string $url): string
    {
        $uri = new Uri($url);
        $port = $uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80);

        return $uri->getScheme().'://'.$uri->getHost().':'.$port;
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

        $response = null;

        try {
            $response = $this->httpClient()->withHeaders([
                'X-License-Key' => $plugin->license,
                'X-Instance-Id' => $instanceId,
                'X-User-Count' => $numberOfUsers,
                'X-Phar-Hash' => $signature,
                'X-Leantime-Version' => $this->appSettings->appVersion,
            ])->get("{$this->marketplaceUrl}/ltmp-api/verify/{$plugin->getIdentifier()}");

        } catch (\Exception $e) {
            Log::error($e);
        }

        // Could not reach the marketplace (transient error); keep the plugin enabled.
        if ($response === null) {
            return true;
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

        $plugin = $this->pluginRepository->getPlugin($pluginId);

        $this->notifyAdmins(
            $pluginId,
            'plugin_license',
            sprintf(__('notification.plugin_disabled_license'), $plugin->name)
        );
    }

    /**
     * Disables a plugin that keeps failing requests (called by the core plugin crash breaker)
     * and tells the administrators why.
     *
     * Only the local switch is flipped: the marketplace license stays activated, so turning the
     * plugin back on after a fix needs no new activation. System plugins (from config) can't be
     * disabled here.
     *
     * Not @api: only the crash breaker calls it.
     *
     * @param  string  $pluginFolder  The plugin's folder name.
     * @param  string  $lastError  The message of the error that tripped the breaker.
     * @return bool Whether a plugin was disabled.
     */
    public function disableAfterFailures(string $pluginFolder, string $lastError): bool
    {
        $plugin = collect($this->pluginRepository->getAllPlugins(true))
            ->first(fn (InstalledPlugin $installed) => strcasecmp($installed->foldername, $pluginFolder) === 0);

        if ($plugin === null) {
            Log::error('Plugin '.$pluginFolder.' keeps failing requests but is not a disableable plugin (system plugin or not installed): '.$lastError);

            return false;
        }

        $this->pluginRepository->disablePlugin($plugin->id);
        $this->clearCache();

        Log::error('Disabled plugin '.$plugin->foldername.' after repeated errors: '.$lastError);
        PluginDisabled::dispatch(plugin: (string) $plugin->foldername);

        $this->notifyAdmins(
            $plugin->id,
            'plugin_failing',
            sprintf(self::translatedOr('notification.plugin_disabled_after_errors', "The plugin '%s' was disabled because it kept causing errors: %s"), $plugin->name, Str::limit($lastError, 200))
        );

        return true;
    }

    /**
     * Sets a plugin's supported range and whether this Leantime version is in it, read from the
     * plugin's composer.json. A plugin without a readable composer.json declares no range, so it
     * gets the undeclared (3.x) range: its folder may still hold a register.php or routes.php.
     *
     * @param  string|null  $leantimeVersion  The version to check against; default the running one.
     * @return InstalledPlugin The same plugin, for chaining.
     */
    private function applyCompatibility(InstalledPlugin $plugin, ?string $leantimeVersion = null): InstalledPlugin
    {
        $composer = $this->readComposer((string) $plugin->foldername) ?? [];

        $plugin->requires = $this->compatibility->rangeFor($composer);
        $plugin->compatible = $this->compatibility->isCompatible($plugin->requires, $leantimeVersion);

        return $plugin;
    }

    /**
     * The decoded composer.json of an installed plugin (folder or phar), or null when missing.
     *
     * @return array<string, mixed>|null
     */
    private function readComposer(string $pluginFolder): ?array
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $pluginFolder) !== 1) {
            return null;
        }

        $pluginPath = Str::finish($this->pluginDirectory, DIRECTORY_SEPARATOR).$pluginFolder.DIRECTORY_SEPARATOR;
        foreach ([$pluginPath.'composer.json', "phar://{$pluginPath}{$pluginFolder}.phar/composer.json"] as $composerPath) {
            if (! @file_exists($composerPath)) {
                continue;
            }
            $composer = json_decode((string) @file_get_contents($composerPath), true);

            return is_array($composer) ? $composer : null;
        }

        return null;
    }

    /**
     * Drops the plugins that don't support this Leantime version from an enabled-plugin list,
     * and tells the administrators once per plugin version and Leantime version. System plugins
     * (from config) are left alone: the deployment that configures them owns their compatibility.
     *
     * @param  array<string, InstalledPlugin>  $plugins  Enabled plugins keyed by folder.
     * @return array<string, InstalledPlugin>
     */
    private function withoutIncompatible(array $plugins): array
    {
        foreach ($plugins as $folder => $plugin) {
            // System plugins come from the deployment's config: whoever configures them owns their compatibility.
            if ($plugin->type === $this->pluginTypes['system'] || $plugin->compatible !== false) {
                continue;
            }

            unset($plugins[$folder]);
            Log::warning('Not loading plugin '.$plugin->foldername.' '.$plugin->version.': it supports Leantime '.$plugin->requires.', this is '.$this->appSettings->appVersion);

            try {
                $this->notifyIncompatibleOnce($plugin);
            } catch (\Throwable $e) {
                Log::warning('Could not notify about incompatible plugin '.$plugin->foldername.': '.$e->getMessage());
            }
        }

        return $plugins;
    }

    /**
     * One admin notification per plugin version and Leantime version, not one per cache rebuild.
     */
    private function notifyIncompatibleOnce(InstalledPlugin $plugin): void
    {
        $settingKey = 'plugins.incompatibleNotified.'.$plugin->foldername;
        $marker = $plugin->version.'|'.$this->appSettings->appVersion;

        if (! isset($plugin->id) || $this->settingsService->getSetting($settingKey) === $marker) {
            return;
        }

        // Several workers rebuild the list at once after an update: only the lock holder sends,
        // and it re-reads the marker inside the lock. The others skip (the holder covers them).
        $lock = Cache::lock('plugins.incompatibleNotify.'.$plugin->foldername, 30);
        if (! $lock->get()) {
            return;
        }

        try {
            if ($this->settingsService->getSetting($settingKey) === $marker) {
                return;
            }

            $this->notifyAdmins(
                $plugin->id,
                'plugin_incompatible',
                sprintf(
                    self::translatedOr('notification.plugin_incompatible_paused', "The plugin '%s' (v%s) doesn't support Leantime %s (it supports %s), so it is not being loaded. Update the plugin to turn it back on."),
                    $plugin->name, $plugin->version, $this->appSettings->appVersion, $plugin->requires)
            );
            // Only after the notification went out, so a failed send is retried on the next rebuild.
            $this->settingsService->saveSetting($settingKey, $marker);
        } finally {
            $lock->release();
        }
    }

    /**
     * The translation of $key, or $english when the language isn't loaded yet (an error early in a
     * request or console boot): an admin notification must never read as a bare language key.
     */
    private static function translatedOr(string $key, string $english): string
    {
        // Without a loaded language the lookup gives the bare key (or nothing at all).
        $text = (string) __($key);

        return $text !== '' && $text !== $key ? $text : $english;
    }

    /**
     * Sends an in-app notification about a plugin to every administrator and owner.
     */
    private function notifyAdmins(int $pluginId, string $type, string $message): void
    {
        $userService = app()->make(UsersService::class);
        $notificationService = app()->make(Notifications::class);

        $admins = collect($userService->getAll())->filter(fn ($user) => in_array((string) $user['role'], Roles::keysAtLeast(Roles::$admin), true));

        $notifications = $admins->map(fn ($admin) => [
            'userId' => $admin['id'],
            'read' => '0',
            'type' => $type,
            'module' => 'plugins',
            'moduleId' => $pluginId,
            'message' => $message,
            'datetime' => dtHelper()->dbNow()->formatDateTimeForDb(), // UTC; date() is the user's tz here (#3201)
            'url' => '/plugins/myapps',
            'authorId' => 1,
        ])->values()->toArray();

        $notificationService->addNotifications($notifications);
    }

    /**
     * The installation-cache key of the enabled-plugin list for the running Leantime version.
     */
    private function enabledPluginsCacheKey(): string
    {
        return 'plugins.enabledPlugins.'.$this->appSettings->appVersion;
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

        $response = null;

        try {
            $response = $this->httpClient()->withHeaders([
                'X-License-Key' => $plugin->license,
                'X-Instance-Id' => $instanceId,
                'X-User-Count' => $numberOfUsers,
                'X-Phar-Hash' => $signature,
                'X-Leantime-Version' => $this->appSettings->appVersion,
            ])->get("{$this->marketplaceUrl}/ltmp-api/deactivate/{$plugin->getIdentifier()}");

        } catch (\Exception $e) {
            Log::error($e);
        }

        // Could not reach the marketplace (transient error); treat as a no-op success.
        if ($response === null) {
            return true;
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
     * Runs a single plugin lifecycle action (install, enable, disable, remove) and
     * returns the notification descriptor describing the outcome.
     *
     * The returned array is a [messageKey, type] pair where messageKey is a language
     * key and type is the notification severity ('success' or 'error'). This keeps the
     * dynamic dispatch and notification-key assembly out of the controller.
     *
     * @param  string  $action  One of: install, enable, disable, remove, update.
     * @param  mixed  $id  The plugin identifier (folder name for install, numeric id otherwise).
     * @return array{0: string, 1: string} A [messageKey, type] notification descriptor.
     *
     * @throws \InvalidArgumentException If the action is not a supported lifecycle action.
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function performPluginAction(string $action, mixed $id): array
    {
        $allowedActions = ['install', 'enable', 'disable', 'remove', 'update'];

        if (! in_array($action, $allowedActions, true)) {
            throw new \InvalidArgumentException("Unsupported plugin action: {$action}");
        }

        $succeeded = $this->{"{$action}Plugin"}($id);

        if (! $succeeded && $action === 'enable') {
            $plugin = $this->pluginRepository->getPlugin((int) $id);
            if ($plugin !== false && $this->applyCompatibility($plugin)->compatible === false) {
                return ['notification.plugin_incompatible', 'error'];
            }
        }

        return $succeeded
            ? ["notification.plugin_{$action}_success", 'success']
            : ["notification.plugin_{$action}_error", 'error'];
    }

    /**
     * Aggregates the CSS contributed by enabled plugins into a single payload.
     *
     * Runs the 'pluginCss' filter to collect the list of plugin CSS files, keeps only
     * the files that actually exist under APP_ROOT/plugins, reads their contents and
     * concatenates them into one string.
     *
     * @return string The combined CSS contents of all registered, existing plugin files.
     *
     * @api
     */
    public function getAggregatedPluginCss(): string
    {
        $cssFiles = self::dispatch_filter('pluginCss', []);

        $cssStrs = collect($cssFiles)
            ->filter(fn ($file) => file_exists(APP_ROOT."/plugins/$file"))
            ->map(fn ($file) => file_get_contents(APP_ROOT."/plugins/$file"))
            ->all();

        return implode('', $cssStrs);
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
        Cache::store('installation')->forget($this->enabledPluginsCacheKey());
        // Permission engine caches — a documented cache clear must bust them too, or a
        // newly-shipped domain/plugin permission class stays invisible (and acts as a recovery
        // path if the provider cache ever goes stale). Provider discovery is code-derived and
        // lives on the installation store; the role->permission map/meta come from this
        // instance's database and live on the instance store, so the engine flushes those.
        Cache::store('installation')->forget('permissionProviders');
        app(\Leantime\Core\Auth\Permissions\PermissionService::class)->flushCache();

        session()->forget('template_paths');
        session()->forget('composers');

        $files = app()->make(\Illuminate\Filesystem\Filesystem::class);
        $viewPathCachePath = storage_path('framework/viewPaths.php');
        $files->delete($viewPathCachePath);

        $composerPathCachePath = storage_path('framework/composerPaths.php');
        $files->delete($composerPathCachePath);

        // Compiled Blade views are invalidated by the source file's mtime, which a phar-packaged
        // plugin does not change across releases, so an upgraded plugin kept executing its OLD
        // templates (including already-fixed crashes) until someone cleared the folder by hand
        // (#3594). Plugin changes are rare, so recompiling everything once is cheap.
        $compiledViewsPath = config('view.compiled');
        if (is_string($compiledViewsPath) && $files->isDirectory($compiledViewsPath)) {
            foreach ($files->glob($compiledViewsPath.'/*.php') as $compiledView) {
                $files->delete($compiledView);
            }
        }
    }
}
