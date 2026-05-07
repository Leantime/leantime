<?php

namespace Leantime\Domain\Oneonone\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Repositories\Oneonone as OneononeRepo;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Single 1:1 session detail page with live HTMX-driven item editing.
 */
class ShowSession extends Controller
{
    private OneononeService $service;

    private OneononeRepo $repo;

    public function init(OneononeService $service, OneononeRepo $repo): void
    {
        $this->service = $service;
        $this->repo = $repo;
    }

    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$commenter, Roles::$editor, Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $id = (int) ($params['id'] ?? 0);
        if ($id === 0) {
            return Frontcontroller::redirect(BASE_URL.'/oneonone/showMy');
        }

        $session = $this->service->getSession($id);
        if ($session === null) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $itemsByType = $this->service->getItemsGrouped($id);

        $this->tpl->assign('session', $session);
        $this->tpl->assign('itemsByType', $itemsByType);
        $this->tpl->assign('itemTypes', $this->repo->itemTypes);
        $this->tpl->assign('sessionStatuses', $this->repo->sessionStatuses);
        $this->tpl->assign('moodValues', $this->repo->moodValues);
        $this->tpl->assign('canEdit', $this->service->canEditSession($session));

        return $this->tpl->display('oneonone.showSession');
    }

    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$commenter, Roles::$editor, Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $id = (int) ($params['id'] ?? 0);
        if ($id === 0) {
            return Frontcontroller::redirect(BASE_URL.'/oneonone/showMy');
        }

        // Update session header (status, mood, summary, notes, meetingDate, title)
        $values = [
            'meetingDate' => $_POST['meetingDate'] ?? null,
            'title' => $_POST['title'] ?? null,
            'mood' => $_POST['mood'] ?? null,
            'status' => $_POST['status'] ?? null,
            'summary' => $_POST['summary'] ?? null,
        ];

        if (isset($_POST['notes'])) {
            $values['notes'] = $_POST['notes'];
        }

        // Strip nulls (do not overwrite with null when the field was absent in the form)
        $values = array_filter($values, fn ($v) => $v !== null);

        if ($this->service->updateSession($id, $values)) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.session_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notification.oneonone.session_save_failed'), 'error');
        }

        return Frontcontroller::redirect(BASE_URL.'/oneonone/showSession/'.$id);
    }
}
