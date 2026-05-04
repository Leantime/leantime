<?php

namespace Leantime\Domain\Oneonone\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manager "Team 1:1s" dashboard - one card per direct report with
 * counts and the latest session, plus a list of recent sessions.
 */
class ShowTeam extends Controller
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

        $teamDashboard = $this->service->getTeamDashboard();
        $recentSessions = array_slice($this->service->getTeamSessions(), 0, 20);
        $allUsers = $this->userService->getAll(true);

        $this->tpl->assign('teamDashboard', $teamDashboard);
        $this->tpl->assign('recentSessions', $recentSessions);
        $this->tpl->assign('allUsers', $allUsers);

        return $this->tpl->display('oneonone.showTeam');
    }
}
