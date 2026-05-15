<?php

namespace Leantime\Domain\ClientPortal\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\ClientPortal\Services\ClientPortal as ClientPortalService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowProject — detailed view of a single project for the client.
 * Shows progress, milestones, team contacts, and their requests.
 */
class ShowProject extends Controller
{
    private ClientPortalService $portalService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init(ClientPortalService $portalService): void
    {
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
        $projectId = (int) ($params['id'] ?? 0);
        $userId    = (int) session('userdata.id');

        if ($projectId === 0) {
            return FrontcontrollerCore::redirect(BASE_URL . '/clientportal/showDashboard');
        }

        $detail = $this->portalService->getProjectDetail($projectId, $userId);

        if ($detail === null) {
            return FrontcontrollerCore::redirect(BASE_URL . '/clientportal/showDashboard');
        }

        $this->tpl->assign('project',    $detail['project']);
        $this->tpl->assign('progress',   $detail['progress']);
        $this->tpl->assign('percent',    $detail['percent']);
        $this->tpl->assign('milestones', $detail['milestones']);
        $this->tpl->assign('contacts',   $detail['contacts']);
        $this->tpl->assign('requests',   $detail['requests']);
        $this->tpl->assign('projectId',  $projectId);

        return $this->tpl->display('clientportal.showProject');
    }
}
