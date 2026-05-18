<?php

namespace Leantime\Domain\Oneonone\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Unified 1:1 Sessions landing page for TL / CM / Admin.
 *
 * Two tabs:
 *  - My 1:1 Sessions      -> sessions where the current user is the EMPLOYEE
 *  - Team 1:1 Sessions    -> sessions where the current user is the MANAGER
 *
 * Replaces the previous split between /oneonone/showMy (employee view)
 * and /oneonone/showTeam (manager view) for users who occupy both roles
 * (e.g. a Team Lead who has direct reports AND reports to a Company Manager).
 */
class Show extends Controller
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

        // ── My 1:1s (employee view) ──
        $mySessions = $this->service->getMySessions();
        $myOpenActions = $this->service->getMyOpenActionItems();

        $myStats = [
            'total' => count($mySessions),
            'upcoming' => 0,
            'completed' => 0,
            'openActions' => count($myOpenActions),
        ];
        foreach ($mySessions as $s) {
            if (($s['status'] ?? '') === 'completed') {
                $myStats['completed']++;
            }
            if (($s['status'] ?? '') === 'scheduled' && ! empty($s['meetingDate'])) {
                try {
                    if (dtHelper()->parseDbDateTime((string) $s['meetingDate'])->isFuture()) {
                        $myStats['upcoming']++;
                    }
                } catch (\Exception) {
                }
            }
        }

        // ── Team 1:1s (manager view) ──
        $teamDashboard = $this->service->getTeamDashboard();
        $teamSessions = array_slice($this->service->getTeamSessions(), 0, 20);

        $this->tpl->assign('mySessions', $mySessions);
        $this->tpl->assign('myOpenActions', $myOpenActions);
        $this->tpl->assign('myStats', $myStats);
        $this->tpl->assign('teamDashboard', $teamDashboard);
        $this->tpl->assign('teamSessions', $teamSessions);

        // Default tab: 'team' if the user has direct reports, otherwise 'my'
        $defaultTab = ! empty($teamDashboard) ? 'team' : 'my';
        if (! empty($params['tab']) && in_array($params['tab'], ['my', 'team'], true)) {
            $defaultTab = $params['tab'];
        }
        $this->tpl->assign('defaultTab', $defaultTab);

        return $this->tpl->display('oneonone.show');
    }
}
