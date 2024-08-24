<?php

namespace Leantime\Views\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Composer;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Menu\Repositories\Menu;

class App extends Composer
{
    use DispatchesEvents;

    public static array $views = [
        'global::layouts.app',
        'global::layouts.entry',
    ];

    private Menu $menuRepo;

    /**
     * @param Menu $menuRepo
     *
     * @return void
     */
    public function init(Menu $menuRepo): void
    {
        $this->menuRepo = $menuRepo;
    }

    /**
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        // These needs to live in the main app since the menu open or closed changes the entire html layout
        if (session()->exists("userdata")) {
            session(["menuState" => $this->menuRepo->getSubmenuState('mainMenu') ?: 'open']);
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
