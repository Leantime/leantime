<?php

namespace Leantime\Domain\ClientPortal\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\ClientPortal\Services\ClientPortal as ClientPortalService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowDashboard — Client's main portal page listing all their projects.
 */
class ShowDashboard extends Controller
{
    private ClientPortalService $portalService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init(ClientPortalService $portalService): void
    {
        // Only commenter (client) role can access; admins/owners can preview.
        $role = session('userdata.role');
        if ($role !== Roles::$commenter && ! Auth::userIsAtLeast(Roles::$admin, true)) {
            FrontcontrollerCore::redirect(BASE_URL . '/dashboard/home');
        }

        $this->portalService = $portalService;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function get(array $params): Response
    {
        $userId   = (int) session('userdata.id');
        $projects = $this->portalService->getProjectsForClient($userId);

        $this->tpl->assign('projects', $projects);

        return $this->tpl->display('clientportal.showDashboard');
    }
}
