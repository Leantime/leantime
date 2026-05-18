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
        Auth::authOrRedirect([Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $currentUserId = (int) (session('userdata.id') ?? 0);

        $allUsers = $this->userService->getAll(true);

        // Determine current user's numeric role level for junior-only filtering.
        $allRoles = Roles::getRoles(); // [5 => 'readonly', 10 => 'commenter', ...]
        $currentRoleLevel = (int) array_search(session('userdata.role') ?? '', $allRoles);
        $isAdmin = Auth::userIsAtLeast(Roles::$admin);

        // Minimum role level an employee must have to appear in the dropdown.
        // commenter (10) = legacy client role, readonly (5) — both are external
        // and must never appear as 1:1 employees.
        $minEmployeeLevel = (int) array_search(Roles::$editor, $allRoles); // 20

        // Exclude self, external/client roles, and — for non-admins — anyone
        // whose role level is equal to or higher than the current user's.
        $allUsers = array_values(array_filter($allUsers, function ($u) use ($currentUserId, $currentRoleLevel, $isAdmin, $minEmployeeLevel) {
            if ((int) ($u['id'] ?? 0) === $currentUserId) {
                return false;
            }
            $userRoleLevel = (int) ($u['role'] ?? 0);
            if ($userRoleLevel < $minEmployeeLevel) {
                return false; // exclude commenter / readonly (client accounts)
            }
            if (! $isAdmin && $userRoleLevel >= $currentRoleLevel) {
                return false; // exclude peers and superiors
            }

            return true;
        }));

        $this->tpl->assign('allUsers', $allUsers);
        $this->tpl->assign('values', [
            'employeeId' => $params['employeeId'] ?? '',
            'meetingDate' => $params['meetingDate'] ?? '',
            'title' => $params['title'] ?? '',
        ]);

        return $this->tpl->display('oneonone.newSession');
    }

    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $values = [
            'employeeId' => $_POST['employeeId'] ?? '',
            'meetingDate' => $_POST['meetingDate'] ?? '',
            'title' => $_POST['title'] ?? '',
            'mood' => $_POST['mood'] ?? null,
        ];

        $id = $this->service->scheduleSession($values);

        if ($id === false) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.schedule_failed'), 'error');

            return Frontcontroller::redirect(BASE_URL . '/oneonone/newSession');
        }

        $this->tpl->setNotification($this->language->__('notification.oneonone.scheduled'), 'success');

        return Frontcontroller::redirect(BASE_URL . '/oneonone/showSession/' . $id);
    }
}
