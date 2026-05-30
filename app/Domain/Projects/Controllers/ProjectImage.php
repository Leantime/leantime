<?php

namespace Leantime\Domain\Projects\Controllers;

use Illuminate\Http\Request;
use Leantime\Core\Http\Responses\ImageResponse;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves and updates project avatars.
 *
 * A native Laravel controller (constructor DI, route-bound actions). Relocated from the
 * retired Api\Controllers\Projects. Bound in Projects/routes.php at the canonical
 * /projects/projectImage/{id} plus the backward-compatible /api/projects alias used by
 * core templates and the plugin submodule. The JSON sort/status operations the old
 * controller also multiplexed onto /api/projects now go through JSON-RPC.
 */
class ProjectImage
{
    public function __construct(private ProjectService $projectService) {}

    /**
     * GET — returns the project avatar as a binary/SVG response.
     *
     * The id comes from the canonical path segment ({id}) or the legacy ?projectAvatar=
     * query param. Avatars are low-sensitivity images rendered across the app (including
     * arbitrary project rows on admin screens), so the read stays open to any authenticated user.
     */
    public function show(Request $request, ?string $id = null): ImageResponse|Response
    {
        $id = $request->query('projectAvatar', $id);

        if (empty($id)) {
            return response()->json(['status' => 'failure'], 400);
        }

        return new ImageResponse($this->projectService->getProjectAvatar($id));
    }

    /**
     * POST — uploads a new avatar for the active project.
     *
     * The avatar always targets session('currentProject'); the upload requires manage
     * rights on that project (the legacy endpoint had no role check at all).
     */
    public function upload(): Response
    {
        if (! isset($_FILES['file'])) {
            return response()->json(['error' => 'File not included'], 400);
        }

        $projectId = (int) session('currentProject');

        if (! $this->projectService->userCanManageProject($projectId)) {
            return response()->json(['status' => 'unauthorized'], 403);
        }

        $_FILES['file']['name'] = 'profileImage-'.$projectId.'.png';

        $this->projectService->setProjectAvatar($_FILES, $projectId);

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return response()->json(['status' => 'ok']);
    }
}
