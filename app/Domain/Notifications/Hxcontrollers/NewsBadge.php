<?php

namespace Leantime\Domain\Notifications\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\HtmxController;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class NewsBadge extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'notifications::partials.newsBadge';

    private \Leantime\Domain\Notifications\Services\News $newsService;



    /**
     * Controller constructor
     *
     * @param Timesheets                              $timesheetService
     * @param Menu                                    $menuService
     * @param \Leantime\Domain\Menu\Repositories\Menu $menuRepo
     * @return void
     */
    public function init(\Leantime\Domain\Notifications\Services\News $newsService): void
    {
        $this->newsService = $newsService;

    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function get()
    {

        try {
            $hasNews = $this->newsService->hasNews($_SESSION['userdata']['id']);
        }catch(\Exception $e) {
            error_log($e);
            $hasNews = false;
        }

        $this->tpl->assign('hasNews', $hasNews);
    }

}
