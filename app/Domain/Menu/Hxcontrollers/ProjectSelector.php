<?php

namespace Leantime\Domain\Menu\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Timesheets\Services\Timesheets;

class ProjectSelector extends HtmxController
{
    use DispatchesEvents;

    protected static string $view = 'menu::partials.projectSelector';

    private Timesheets $timesheetService;

    private Menu $menuService;

    /**
     * Controller constructor
     */
    public function init(Timesheets $timesheetService, Menu $menuService): void
    {
        $this->timesheetService = $timesheetService;
        $this->menuService = $menuService;
    }

    /**
     * @throws BindingResolutionException
     */
    public function updateMenu(): void
    {
        $projectSelectFilter = [
            'groupBy' => $_POST['groupBy'] ?? 'none',
            'client' => (int) ($_POST['client'] ?? null),
        ];

        $userId = session()->exists('userdata') ? (int) session('userdata.id') : null;

        $viewData = $this->menuService->getProjectSelectorViewData(
            $userId,
            $projectSelectFilter,
            FrontcontrollerCore::getCurrentRoute(),
            $this->incomingRequest->getRequestUri()
        );

        array_map([$this->tpl, 'assign'], array_keys($viewData), array_values($viewData));

        $this->tpl->assign('module', FrontcontrollerCore::getModuleName());
        $this->tpl->assign('action', FrontcontrollerCore::getActionName());
    }

    public function filter(): void {}
}
