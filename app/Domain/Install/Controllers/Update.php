<?php

namespace Leantime\Domain\Install\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Install\Services\Install as InstallService;
use Symfony\Component\HttpFoundation\Response;

class Update extends Controller
{
    private InstallService $installService;

    /**
     * init - initialize private variables
     */
    public function init(InstallService $installService)
    {
        $this->installService = $installService;
    }

    /**
     * get - handle get requests
     *
     * @params parameters or body of the request
     */
    public function get($params)
    {
        if (! $this->installService->needsUpdate()) {
            return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
        }

        $updatePage = self::dispatch_filter('customUpdatePage', 'install.update');

        return $this->tpl->display($updatePage, 'entry');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post($params): Response
    {
        if (isset($_POST['updateDB'])) {
            $success = $this->installService->runUpdate();

            if (is_array($success) === true) {
                foreach ($success as $errorMessage) {
                    $this->tpl->setNotification('There was a problem. Please reach out to support@leantime.io for assistance.', 'error');
                    // report($errorMessage);
                }
                $this->tpl->setNotification('There was a problem updating your database. Please check your error logs to verify your database is up to date.', 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/install/update');
            }

            if ($success === true) {
                return FrontcontrollerCore::redirect(BASE_URL);
            }
        }

        $this->tpl->setNotification('There was a problem. Please reach out to support@leantime.io for assistance.', 'error');

        return FrontcontrollerCore::redirect(BASE_URL.'/install/update');
    }
}
