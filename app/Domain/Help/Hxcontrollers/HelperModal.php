<?php

namespace Leantime\Domain\Help\Hxcontrollers;

use Leantime\Core\Http\Controller\HtmxController;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Users\Services\Users as UserService;

class HelperModal extends HtmxController
{
    protected static string $view = '';

    protected Helper $helperService;

    protected UserService $userService;

    /**
     * Controller constructor
     *
     * @param  \Leantime\Domain\Projects\Services\Projects  $projectService  The projects domain service.
     * @return void
     */
    public function init(
        Helper $helperService,
        UserService $userService
    ) {
        $this->helperService = $helperService;
        $this->userService = $userService;
    }

    public function get() {}

    public function dontShowAgain($params)
    {

        $modal = $params['modalId'] ?? '';
        $hidePermanently = ($params['hidePermanently'] ?? false) === 'on' ? true : false;

        if ($modal !== '') {
            $this->userService->updateUserSettings('modals', $modal, $hidePermanently);
        }

        return $this->tpl->emptyResponse();
    }
}
