<?php

namespace Leantime\Domain\Install\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Install\Services\Install as InstallService;
use Symfony\Component\HttpFoundation\Response;

class Index extends Controller
{
    private InstallService $installService;

    /**
     * init - initialize private variables
     *
     * @throws HttpResponseException
     */
    public function init(InstallService $installService)
    {
        $this->installService = $installService;

        if ($this->installService->isInstalled()) {
            return FrontcontrollerCore::redirect(BASE_URL.'/');
        }
    }

    /**
     * get - handle get requests
     *
     * @param  $params  parameters or body of the request
     */
    public function get($params)
    {
        return $this->tpl->display('install.new', 'entry');
    }

    /**
     * post - process the installation form submission
     *
     * @param  array  $params  parameters or body of the request
     */
    public function post($params): Response
    {
        if (isset($_POST['install'])) {
            $values = [
                'email' => ($params['email']),
                'firstname' => ($params['firstname']),
                'lastname' => ($params['lastname']),
                'company' => ($params['company']),
            ];

            try {
                $this->installService->validateInstallInput($values);
            } catch (\InvalidArgumentException $e) {
                $this->tpl->setNotification($e->getMessage(), 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/install');
            }

            if ($this->installService->runInstall($values)) {
                $this->tpl->setNotification(sprintf($this->language->__('notifications.installation_success_setup_account'), BASE_URL), 'success');

                if (session()->has('pwReset')) {
                    return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.session('pwReset'));
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.error_installing'), 'error');
            }
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/install');
    }
}
