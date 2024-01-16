<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;
use Leantime\Core\Environment;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Theme;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class App extends Composer
{
    Use Eventhelpers;

    public static array $views = [
        'global::layouts.app',
        'global::layouts.entry',
    ];

    private Menu $menuRepo;

    /**
     * @param Menu        $menuRepo
     * @return void
     */
    public function init(Menu $menuRepo): void {
        $this->menuRepo = $menuRepo;
    }

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

        $announcement = null;
        $announcement = self::dispatch_filter("appAnnouncement", $announcement);

        return [
            "section" => $menuType,
            "appAnnouncement" => $announcement,
        ];
    }
}
