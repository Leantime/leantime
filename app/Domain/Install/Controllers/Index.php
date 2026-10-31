<?php

namespace Leantime\Domain\Install\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Install\Services\Install as InstallService;
use Symfony\Component\HttpFoundation\Response;

class Index extends Controller
{
    private InstallService $installService;

    /**
     * init - initialize private variables
     *
     * @param  InstallService  $installService  The install service.
     */
    public function init(InstallService $installService): void
    {
        $this->installService = $installService;
    }

    /**
     * get - shows the installation form, or sends the visitor on when Leantime is already installed
     *
     * @param  array  $params  parameters or body of the request
     */
    public function get($params): Response
    {
        if ($this->installService->isInstalled()) {
            return $this->redirectInstalled();
        }

        return $this->tpl->display('install.new', 'entry');
    }

    /**
     * post - process the installation form submission
     *
     * @param  array  $params  parameters or body of the request
     */
    public function post($params): Response
    {
        if ($this->installService->isInstalled()) {
            return $this->redirectInstalled();
        }

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
                // The installer is the first trustworthy moment to learn the URL email links use.
                // Optional: the installation is done, so a failure here must not hide the setup link.
                try {
                    app()->make(TrustedAppUrl::class)->learnFromInstall($this->incomingRequest);
                } catch (\Throwable $e) {
                    Log::error($e);
                }

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

    /**
     * redirectInstalled - where /install sends a visitor once Leantime is installed.
     *
     * Normally to the start page. When the owner the installer created was never set up (the
     * redirect after the install got lost, e.g. to a proxy timeout), back into that owner's
     * onboarding, which is the only way the owner can get a password and become active.
     */
    private function redirectInstalled(): Response
    {
        $ownerSetupToken = $this->installService->resumeOwnerSetup();

        if ($ownerSetupToken !== null) {
            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$ownerSetupToken);
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/');
    }
}
