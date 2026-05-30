<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Support\ImageResponse;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves and updates project avatars.
 *
 * Relocated from the retired Api\Controllers\Projects so the behaviour lives in the
 * Projects domain. Reachable at the canonical /projects/projectImage/{id} and at the
 * backward-compatible /api/projects alias (registered in Projects/routes.php) used by
 * core templates and the plugin submodule. The JSON sort/status operations that the
 * old controller also multiplexed onto /api/projects now go through JSON-RPC.
 */
class ProjectImage extends Controller
{
    private ProjectService $projectService;

    public function init(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
    }

    /**
     * GET — returns the project avatar as a binary/SVG response.
     *
     * Accepts the id from the canonical path segment ({id}) or the legacy
     * ?projectAvatar= query param. Avatars are low-sensitivity images rendered
     * across the app (including arbitrary project rows on admin screens), so the
     * read stays available to any authenticated user, matching prior behaviour.
     */
    public function get(array $params): Response
    {
        $id = $params['projectAvatar'] ?? $params['id'] ?? null;

        if (empty($id)) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        return ImageResponse::make($this->projectService->getProjectAvatar($id));
    }

    /**
     * POST — uploads a new avatar for the active project.
     *
     * The avatar always targets session('currentProject'); the upload requires
     * manage rights on that project (the legacy endpoint had no role check at all).
     */
    public function post(array $params): Response
    {
        if (! isset($_FILES['file'])) {
            return $this->tpl->displayJson(['error' => 'File not included'], 400);
        }

        $projectId = (int) session('currentProject');

        if (! $this->projectService->userCanManageProject($projectId)) {
            return $this->tpl->displayJson(['status' => 'unauthorized'], 403);
        }

        $_FILES['file']['name'] = 'profileImage-'.$projectId.'.png';

        $this->projectService->setProjectAvatar($_FILES, $projectId);

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return $this->tpl->displayJson(['status' => 'ok']);
    }
}
