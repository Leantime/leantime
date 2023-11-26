<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;
use Leantime\Core\AppSettings;
use Leantime\Core\Environment;
use Leantime\Core\Controller;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Theme;
use Exception;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class App extends Composer
{
    public static array $views = [
        'global::layouts.app',
        'global::layouts.entry',
    ];
    private Environment $config;
    private Theme $themeCore;
    private Setting $settingsRepo;
    private Menu $menuRepo;

    /**
     * @param Menu        $menuRepo
     * @param Setting     $settingsRepo
     * @param Theme       $themeCore
     * @param Environment $config
     * @return void
     */
    public function init(
        Menu $menuRepo,
        Setting $settingsRepo,
        Theme $themeCore,
        Environment $config
    ): void {
        $this->menuRepo = $menuRepo;
        $this->settingsRepo = $settingsRepo;
        $this->themeCore = $themeCore;
        $this->config = $config;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function with(): array
    {

        //These needs to live in the main app since menu open or closed changes the entire html layout
        if (isset($_SESSION["userdata"]["id"])) {
            $_SESSION['menuState'] = $this->menuRepo->getSubmenuState('mainMenu') ?: 'open';
        }



        $menuType = $this->menuRepo->getSectionMenuType(FrontcontrollerCore::getCurrentRoute(), "project");


        return [
            "section" => $menuType,
        ];
    }
}
