<?php

namespace Leantime\Domain\Oneonone\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Delete a 1:1 session (manager owner or admin).
 */
class DelSession extends Controller
{
    private OneononeService $service;

    public function init(OneononeService $service): void
    {
        $this->service = $service;
    }

    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$manager, Roles::$admin, Roles::$owner], true);

        $id = (int) ($params['id'] ?? 0);
        $session = $id > 0 ? $this->service->getSession($id) : null;

        if ($session === null) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $this->tpl->assign('session', $session);

        return $this->tpl->display('oneonone.delSession');
    }

    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$manager, Roles::$admin, Roles::$owner], true);

        $id = (int) ($params['id'] ?? 0);

        if ($id > 0 && isset($_POST['del']) && $this->service->deleteSession($id)) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.deleted'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notification.oneonone.delete_failed'), 'error');
        }

        return Frontcontroller::redirect(BASE_URL.'/oneonone/showTeam');
    }
}
