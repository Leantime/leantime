<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Widgets\Services\Widgets as WidgetService;

class MyProjects extends HtmxController
{
    protected static string $view = 'widgets::partials.myProjects';

    private WidgetService $widgetService;

    private Menu $menuService;

    /**
     * Initializes dependencies.
     */
    public function init(
        WidgetService $widgetService,
        Menu $menuService
    ): void {
        $this->widgetService = $widgetService;
        $this->menuService = $menuService;

        session(['lastPage' => BASE_URL.'/dashboard/home']);
    }

    public function get(): void
    {
        $widgetData = $this->widgetService->getMyProjectsWidgetData((int) session('userdata.id'));

        $this->tpl->assign('background', $_GET['noBackground'] ?? '');
        $this->tpl->assign('type', $_GET['type'] ?? 'simple');
        $this->tpl->assign('projectTypeAvatars', $this->menuService->getProjectTypeAvatars());
        $this->tpl->assign('allProjects', $widgetData['projects']);
    }
}
