<?php

namespace Leantime\Domain\Oneonone\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schedule a new 1:1 session.
 */
class NewSession extends Controller
{
    private OneononeService $service;

    private UserService $userService;

    public function init(OneononeService $service, UserService $userService): void
    {
        $this->service = $service;
        $this->userService = $userService;
    }

    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$manager, Roles::$admin, Roles::$owner], true);

        $allUsers = $this->userService->getAll(true);
        // Drop the current user from the employee list to prevent self-1:1
        $currentUserId = (int) (session('userdata.id') ?? 0);
        $allUsers = array_values(array_filter($allUsers, fn ($u) => (int) ($u['id'] ?? 0) !== $currentUserId));

        $this->tpl->assign('allUsers', $allUsers);
        $this->tpl->assign('values', [
            'employeeId' => $params['employeeId'] ?? '',
            'meetingDate' => '',
            'title' => '',
        ]);

        return $this->tpl->display('oneonone.newSession');
    }

    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$manager, Roles::$admin, Roles::$owner], true);

        $values = [
            'employeeId' => $_POST['employeeId'] ?? '',
            'meetingDate' => $_POST['meetingDate'] ?? '',
            'title' => $_POST['title'] ?? '',
            'mood' => $_POST['mood'] ?? null,
        ];

        $id = $this->service->scheduleSession($values);

        if ($id === false) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.schedule_failed'), 'error');

            return Frontcontroller::redirect(BASE_URL.'/oneonone/newSession');
        }

        $this->tpl->setNotification($this->language->__('notification.oneonone.scheduled'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/oneonone/showSession/'.$id);
    }
}
