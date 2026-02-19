<?php

namespace Leantime\Views\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\UI\Composer;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Menu\Repositories\Menu;

class App extends Composer
{
    use DispatchesEvents;

    public static array $views = [
        'global::layouts.app',
    ];

    private Menu $menuRepo;

    private Theme $themeCore;

    public function init(Menu $menuRepo, Theme $themeCore): void
    {
        $this->menuRepo = $menuRepo;
        $this->themeCore = $themeCore;
    }

    /**
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        // These needs to live in the main app since the menu open or closed changes the entire html layout
        if (session()->exists('userdata')) {
            session(['menuState' => $this->menuRepo->getSubmenuState('mainMenu') ?: 'open']);
        }

        $menuType = $this->menuRepo->getSectionMenuType(FrontcontrollerCore::getCurrentRoute(), 'project');

        $announcement = null;
        $announcement = self::dispatch_filter('appAnnouncement', $announcement);

        return [
            'module' => strtolower(FrontcontrollerCore::getModuleName()),
            'section' => $menuType,
            'appAnnouncement' => $announcement,
            'themeBgUrl' => $this->themeCore->getBackgroundImage(),
        ];
    }
}
