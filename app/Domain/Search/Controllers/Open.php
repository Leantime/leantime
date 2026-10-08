<?php

namespace Leantime\Domain\Search\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Search\Services\Search as SearchService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Opens a search result: GET /search/open?type=tickets&id=12
 *
 * Most entity pages only show entities of the session's current project, so a result from
 * another project needs the project switched first. This is the single hop that resolves
 * the entity's real project, authorizes against it, switches, and redirects.
 */
class Open extends Controller
{
    private SearchService $searchService;

    private ProjectService $projectService;

    public function init(SearchService $searchService, ProjectService $projectService): void
    {
        $this->searchService = $searchService;
        $this->projectService = $projectService;
    }

    /**
     * Authorize, switch project when needed and redirect to the entity.
     */
    public function get(array $params): Response
    {
        $type = (string) ($params['type'] ?? '');
        $id = (int) ($params['id'] ?? 0);

        $target = ($type !== '' && $id > 0) ? $this->searchService->resolveTarget($type, $id) : null;

        if ($target === null) {
            return Frontcontroller::redirect(BASE_URL.'/errors/error404');
        }

        if ($target->projectId !== null) {
            $userId = (int) session('userdata.id');

            if (! $this->projectService->isUserAssignedToProject($userId, $target->projectId)) {
                return Frontcontroller::redirect(BASE_URL.'/errors/error404');
            }

            if ((int) session('currentProject') !== $target->projectId) {
                $this->projectService->changeCurrentSessionProject($target->projectId);
            }
        }

        return Frontcontroller::redirect(BASE_URL.$target->url);
    }
}
