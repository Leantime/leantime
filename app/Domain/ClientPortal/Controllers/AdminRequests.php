<?php

namespace Leantime\Domain\ClientPortal\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\ClientPortal\Services\ClientPortal as ClientPortalService;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminRequests — shows all client requests to TL/CM/Admin.
 *
 * GET /clientportal/adminRequests
 */
class AdminRequests extends Controller
{
    private ClientPortalService $portalService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init(ClientPortalService $portalService): void
    {
        if (! Auth::userIsAtLeast(Roles::$teamlead, true)) {
            Frontcontroller::redirect(BASE_URL . '/errors/error403');
        }

        $this->portalService = $portalService;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function get(array $_params): Response
    {
        $filterProjectId = isset($_GET['projectId']) ? (int) $_GET['projectId'] : 0;

        $requests = $this->portalService->getAllRequests($filterProjectId);

        // Group by project
        $grouped = [];
        foreach ($requests as $req) {
            $grouped[$req['projectName'] ?? 'Unknown'][] = $req;
        }

        $this->tpl->assign('grouped', $grouped);
        $this->tpl->assign('totalOpen', count(array_filter($requests, fn ($r) => $r['status'] === 'open')));
        $this->tpl->assign('filterProjectId', $filterProjectId);

        return $this->tpl->display('clientportal.adminRequests');
    }
}
