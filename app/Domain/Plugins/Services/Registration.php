<?php

namespace Leantime\Domain\Plugins\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language;

class Registration
{
    // Plugin Id: folder name of the plugin
    private string $pluginId;

    private bool $distFolderRegistered = false;

    public function __construct(string $pluginId)
    {
        $this->pluginId = $pluginId;

        $this->registerManifestFolder();
    }

    public function registerMiddleware(array $middleware)
    {

        EventDispatcher::add_filter_listener('leantime.core.middleware.loadplugins.handle.pluginsEvents',
            function (array $existing) use ($middleware) {
                return array_merge($existing, $middleware);
            }
        );

        EventDispatcher::add_filter_listener('leantime.core.http.httpkernel.*.plugins_middleware',
            function (array $existing) use ($middleware) {
                return array_merge($existing, $middleware);
            }
        );

    }

    public function registerLanguageFiles(array $languages = [])
    {
        $pluginId = $this->pluginId;

        if (empty($languages)) {
            $languages = $this->findLanguageFiles();
        }

        EventDispatcher::add_event_listener('leantime.core.middleware.loadplugins.handle.pluginsEvents', function () use ($languages) {

            $language = app()->make(Language::class);
            $config = app()->make(Environment::class);
            $currentUserLanguage = session('usersettings.language');

            // At this point in the stack localization has already determined user language and set up the core language
            // array in the language of the users choice. First we register english if it is in the array and then we
            // override with the user language
            if (in_array('en-US', $languages)) {
                $pluginLangArray = $this->loadPluginLanguage('en-US');
                $language->mergeLanguageArray($pluginLangArray);
            }

            // Now check the user language and override if needed
            if (in_array($currentUserLanguage, $languages)) {
                $pluginLangArray = $this->loadPluginLanguage($currentUserLanguage);
                $language->mergeLanguageArray($pluginLangArray);
            }

        }, 5);

    }

    private function findLanguageFiles(): array
    {
        $pluginPath = APP_ROOT.'/app/Plugins/';
        $languageDir = '/Language/';

        // Check both possible locations for language files
        $pharPath = "phar://{$pluginPath}{$this->pluginId}/{$this->pluginId}.phar".$languageDir;
        $regularPath = "{$pluginPath}{$this->pluginId}".$languageDir;

        $languageFiles = [];

        // Check regular directory first
        if (is_dir($regularPath)) {
            $files = scandir($regularPath);
            foreach ($files as $file) {
                if (substr($file, -4) === '.ini') {
                    $languageFiles[] = substr($file, 0, -4);
                }
            }
        }

        // Check phar if no files found in regular directory
        if (empty($languageFiles) && file_exists($pharPath)) {
            $files = scandir($pharPath);
            foreach ($files as $file) {
                if (substr($file, -4) === '.ini') {
                    $languageFiles[] = substr($file, 0, -4);
                }
            }
        }

        return ! empty($languageFiles) ? $languageFiles : [];

    }

    private function loadPluginLanguage($language): array|false
    {

        if (Cache::store('installation')->has($this->pluginId.'.language.'.$language)) {
            return Cache::store('installation')->get($this->pluginId.'.language.'.$language);
        }

        $pluginPath = APP_ROOT.'/app/Plugins/';

        $pharPath = "phar://{$pluginPath}{$this->pluginId}/{$this->pluginId}.phar";
        $regularPath = "{$pluginPath}{$this->pluginId}";

        $languagePath = "/Language/{$language}.ini";

        // Check phar first
        if (file_exists($pharPath.$languagePath)) {
            $completeLanguagePath = $pharPath.$languagePath;
        } elseif (file_exists($regularPath.$languagePath)) {
            $completeLanguagePath = $regularPath.$languagePath;
        } else {
            // Language file doesn't exist
            Cache::store('installation')->set($this->pluginId.'.language.'.$language, false, Carbon::now()->addDays(7));

            return false;
        }

        $languageArray = parse_ini_file($completeLanguagePath, true);

        // We're caching the results no matter what, language file is not going to magically appear.
        // So even a false is valid as parse_ini_is too expensive to run every time
        Cache::store('installation')->set($this->pluginId.'.language.'.$language, $languageArray, Carbon::now()->addDays(7));

        return $languageArray;

    }

    private function getPluginBasePath() {

        $pluginPath = APP_ROOT.'/app/Plugins/';

        $pharPath = "phar://{$pluginPath}{$this->pluginId}/{$this->pluginId}.phar";
        $regularPath = "{$pluginPath}{$this->pluginId}";

        if(file_exists($pharPath)) {
            return $pharPath;
        }

        if(file_exists($regularPath)) {
            return $regularPath;
        }

        return "/";


    }

    public function addMenuItem(array $item, string $section, array $location)
    {

        $pluginId = $this->pluginId;

        EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.'.$section,
            function ($menu) use ($item, $location, $pluginId) {

                // Prepare
                $item['title'] = "<span class='".$item['icon']."'></span> ".__($item['title']);
                $item['tooltip'] = __($item['tooltip']);
                $item['type'] = 'item';
                $item['module'] = $pluginId;

                $primaryLocationKey = $location[0] ?? $menu[count($menu)];

                if (count($location) <= 1) {
                    $menu[$primaryLocationKey] = $item;
                }

                if (count($location) == 2) {
                    $submenuLocationKey = $location[1] ?? $menu[$primaryLocationKey]['submenu'][count(
                        $menu[$primaryLocationKey]['submenu']
                    )];

                    $menu[$primaryLocationKey]['submenu'][$submenuLocationKey] = $item;

                }

                return $menu;
            },
            50
        );

        EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getSectionMenuType.menuSections',
            function ($routes) use ($section, $item) {

                $route = str_replace('/', '.', $item['href']);

                $array = explode('.', $route);
                if ($route[0] == '.') {
                    array_shift($array);
                }
                $route = implode('.', $array);

                return array_merge($routes, [$route => $section]);
            },
        );

    }

    public function addHeaderJs(array $jsFiles)
    {

        EventDispatcher::add_event_listener('leantime.*.afterLinkTags', function () use ($jsFiles) {

            $mix = app()->make(\Leantime\Core\Support\Mix::class);
            $basePath = $this->getPluginBasePath();

            // Add jQuery UI for draggable and resizable functionality
            $files = $mix->getManifest()[$basePath.'/dist'];

            foreach ($jsFiles as $jsFile) {
                if (isset($files[$jsFile])) {
                    echo '<script src="'.BASE_URL.$files[$jsFile].'"></script>';
                }
            }

        });

    }

    protected function registerManifestFolder() {

        if($this->distFolderRegistered === false) {

            $distPath = "";
            $basePath = $this->getPluginBasePath();
            if (file_exists($basePath."/dist")) {
                $distPath = $basePath . "/dist";
            }

            if($distPath !== '') {
                EventDispatcher::add_filter_listener(
                    'leantime.core.support.mix.__construct.mix_manifest_directories',
                    function (array $directories) use ($distPath) {
                        return array_merge($directories, [$distPath]);
                    }
                );

                $this->distFolderRegistered = true;
            }

        }
    }

    public function addFooterJs(array $paths) {

        EventDispatcher::add_event_listener('leantime.*.beforeBodyClose', function () use ($paths) {

            $mix = app()->make(\Leantime\Core\Support\Mix::class);
            $basePath = $this->getPluginBasePath();

            // Add jQuery UI for draggable and resizable functionality
            $files = $mix->getManifest()[$basePath.'/dist'];

            foreach ($paths as $jsFile) {
                if (isset($files[$jsFile])) {
                    echo '<script src="'.BASE_URL.$files[$jsFile].'"></script>';
                }
            }

        });

    }

    public function addCss(array $paths) {

        EventDispatcher::add_event_listener('leantime.*.afterLinkTags', function () use ($paths) {

            $mix = app()->make(\Leantime\Core\Support\Mix::class);
            $basePath = $this->getPluginBasePath();

            // Add jQuery UI for draggable and resizable functionality
            $files = $mix->getManifest()[$basePath.'/dist'];

            foreach ($paths as $cssFile) {
                if (isset($files[$cssFile])) {
                    echo '<link rel="stylesheet" href="'.BASE_URL.$files[$cssFile].'" />';
                }
            }

        });

    }


}
