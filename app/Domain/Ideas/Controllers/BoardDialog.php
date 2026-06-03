<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Ideas\Permissions\IdeasPermissions;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class BoardDialog extends Controller
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = '??';

    private ProjectService $projectService;

    private IdeaService $ideaService;

    /**
     * Initializes dependencies.
     */
    public function init(ProjectService $projectService, IdeaService $ideaService): void
    {
        $this->projectService = $projectService;
        $this->ideaService = $ideaService;
    }

    /**
     * Displays the board dialog form.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(IdeasPermissions::VIEW)]
    public function get(array $params): Response
    {
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            $currentCanvasId = (int) $params['id'];
            $canvasTitle = $this->ideaService->getBoardTitle($currentCanvasId);
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);
        }

        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasname', 'idea');
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('ideas.boardDialog');
        }

        return new Response;
    }

    /**
     * Handles board creation and editing.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(IdeasPermissions::VIEW)]
    public function post(array $params): Response
    {
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            $currentCanvasId = (int) $params['id'];
            $canvasTitle = $this->ideaService->getBoardTitle($currentCanvasId);
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);
        }

        if (isset($_POST['newCanvas'])) {
            $result = $this->handleNewCanvas($currentCanvasId);
            if ($result !== null) {
                return $result;
            }
        }

        if (isset($_POST['editCanvas']) && $currentCanvasId > 0) {
            $result = $this->handleEditCanvas($currentCanvasId);
            if ($result !== null) {
                return $result;
            }
        }

        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasname', 'idea');
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('ideas.boardDialog');
        }

        return new Response;
    }

    /**
     * Handles creating a new board.
     */
    private function handleNewCanvas(int|string &$currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        $currentCanvasId = $this->ideaService->createBoardFromDialog(
            $_POST['canvastitle'],
            (int) session('currentProject'),
            (int) session('userdata.id')
        );

        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success', static::CANVAS_NAME.'board_created');
        session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

        return Frontcontroller::redirect(BASE_URL.'/ideas/boardDialog/'.$currentCanvasId);
    }

    /**
     * Handles editing a board title.
     */
    private function handleEditCanvas(int $currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        $this->ideaService->updateBoard($currentCanvasId, $_POST['canvastitle']);

        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/ideas/boardDialog/'.$currentCanvasId);
    }
}
