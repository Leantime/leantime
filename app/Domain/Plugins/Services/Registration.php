<?php

namespace Leantime\Domain\Plugins\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language;

class Registration
{
    //Plugin Id: folder name of the plugin
    private string $pluginId;

    public function __construct(string $pluginId)
    {
        $this->pluginId = $pluginId;
    }

    public function registerMiddleware(array $middleware)
    {

        EventDispatcher::add_filter_listener('leantime.core.middleware.loadplugins.handle.pluginMiddlware',
            function (array $existing) use ($middleware) {
                return array_merge($existing, $middleware);
            }
        );

    }

    public function registerLanguageFiles(array $languages)
    {

        $pluginId = $this->pluginId;

        EventDispatcher::add_event_listener('leantime.core.middleware.loadplugins.handle.pluginsEvents', function () use ($pluginId, $languages) {

            $language = app()->make(Language::class);
            $config = app()->make(Environment::class);

            try {
                $languageArray = Cache::store('installation')->get($pluginId.'.languageArray', []);
            } catch (\Exception $e) {
                Log::error($e);
                $languageArray = [];
            }

            if (is_array($languageArray) && count($languageArray) > 0) {
                $language->ini_array = array_merge($language->ini_array, $languageArray);

                return;
            }

            //Always load en-Us as this is the default fallback language
            if (! Cache::store('installation')->has($pluginId.'.language.en-US')) {

                if(file_exists(app_path().'/Plugins/'.$pluginId.'/Language/en-US.ini')) {
                    $languageArray += parse_ini_file(
                        app_path() . '/Plugins/' . $pluginId . '/Language/en-US.ini',
                        true
                    );
                }
            }

            if ((($userLanguage = session('usersettings.language') ?? $config->language) !== 'en-US') && in_array($userLanguage, $languages)) {

                if (! Cache::store('installation')->has($pluginId.'.language.'.$userLanguage)) {

                    if(file_exists(app_path().'/Plugins/'.$pluginId.'/Language/'.$userLanguage.'.ini')) {
                        Cache::store('installation')->put(
                            $pluginId.'.language.'.$userLanguage,
                            parse_ini_file(app_path().'/Plugins/'.$pluginId.'/Language/'.$userLanguage.'.ini', true),
                            Carbon::now()->addDays(30)
                        );
                    }
                }

                $languageArray = array_merge($languageArray, Cache::store('installation')->get($pluginId.'.language.'.$language));

                $cachedLangArr = Cache::store('installation')->get($pluginId.'.language.'.$language, []);
                $languageArray = array_merge(
                    is_array($languageArray) ? $languageArray : [],
                    is_array($cachedLangArr) ? $cachedLangArr : []
                );
            }

            try {
                Cache::store('installation')->put($pluginId.'.languageArray', $languageArray);
            } catch (\Exception $e) {
                Log::error($e);
            }

            $language->ini_array = array_merge($language->ini_array, $languageArray);

        }, 5);

    }

    public function addMenuItem(array $item, string $section, array $location)
    {

        $pluginId = $this->pluginId;

        EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.'.$section,
            function ($menu) use ($item, $location, $pluginId) {

                //Prepare
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

    public function addHeaderJs(array $middleware) {}

    public function addFooterJs(array $middleware) {}

    public function addCss(array $middleware) {}
}
