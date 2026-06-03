<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Ideas\Permissions\IdeasPermissions;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Symfony\Component\HttpFoundation\Response;

class DelCanvas extends Controller
{
    private IdeaService $ideaService;

    /**
     * Initializes dependencies.
     */
    public function init(IdeaService $ideaService): void
    {
        $this->ideaService = $ideaService;
    }

    /**
     * Displays the delete idea board confirmation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(IdeasPermissions::DELETE)]
    public function get(array $params): Response
    {
        return $this->tpl->display('ideas.delCanvas');
    }

    /**
     * Handles idea board deletion. The controller gate defers (entityScoped) to the service's
     * deleteCanvas(), which authorizes DELETE against the board's REAL project.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(IdeasPermissions::DELETE, entityScoped: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->ideaService->deleteCanvas($id);

            $this->tpl->setNotification($this->language->__('notification.idea_board_deleted'), 'success', 'ideaboard_deleted');

            return Frontcontroller::redirect(BASE_URL.'/ideas/showBoards');
        }

        return $this->tpl->display('ideas.delCanvas');
    }
}
