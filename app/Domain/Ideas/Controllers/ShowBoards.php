<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class ShowBoards extends Controller
{
    private IdeaService $ideaService;

    private ProjectService $projectService;

    /**
     * Initializes dependencies.
     */
    public function init(IdeaService $ideaService, ProjectService $projectService): void
    {
        $this->ideaService = $ideaService;
        $this->projectService = $projectService;

        session(['lastPage' => CURRENT_URL]);
        session(['lastIdeaView' => 'board']);
    }

    /**
     * Displays the idea boards view.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $allCanvas = $this->ideaService->getAllBoards(session('currentProject'));
        $currentCanvasId = $this->resolveCurrentCanvasId($allCanvas, $params);

        $this->assignTemplateVars($currentCanvasId, $allCanvas);

        if (! isset($_GET['raw'])) {
            return $this->tpl->display('ideas.showBoards');
        }

        return new Response;
    }

    /**
     * Handles idea board mutations (create, edit, search).
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        $allCanvas = $this->ideaService->getAllBoards(session('currentProject'));
        $currentCanvasId = $this->resolveCurrentCanvasId($allCanvas, $params);

        if (isset($_POST['searchCanvas'])) {
            $currentCanvasId = (int) $_POST['searchCanvas'];
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        if (isset($_POST['newCanvas'])) {
            $result = $this->handleNewCanvas($currentCanvasId, $allCanvas);
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

        $this->assignTemplateVars($currentCanvasId, $allCanvas);

        if (! isset($_GET['raw'])) {
            return $this->tpl->display('ideas.showBoards');
        }

        return new Response;
    }

    /**
     * Resolves the current canvas ID from session or request parameters.
     *
     * Auto-creates a default board (via the service) when the project has none.
     */
    private function resolveCurrentCanvasId(array &$allCanvas, array $params): int
    {
        if (! $allCanvas || count($allCanvas) == 0) {
            $currentCanvasId = $this->ideaService->ensureBoardExists(
                (int) session('currentProject'),
                (int) session('userdata.id'),
                $allCanvas
            );
            $allCanvas = $this->ideaService->getAllBoards(session('currentProject'));

            return $currentCanvasId;
        }

        if (session()->exists('currentIdeaCanvas')) {
            $currentCanvasId = session('currentIdeaCanvas');
        } else {
            $currentCanvasId = -1;
            session(['currentIdeaCanvas' => '']);
        }

        if (count($allCanvas) > 0 && session('currentIdeaCanvas') == '') {
            $currentCanvasId = $allCanvas[0]['id'];
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        if (isset($params['id'])) {
            $currentCanvasId = (int) $params['id'];
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        return $currentCanvasId;
    }

    /**
     * Handles creating a new idea board.
     */
    private function handleNewCanvas(int &$currentCanvasId, array &$allCanvas): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        $currentCanvasId = $this->ideaService->createBoard(
            $_POST['canvastitle'],
            (int) session('currentProject'),
            (int) session('userdata.id')
        );
        $allCanvas = $this->ideaService->getAllBoards(session('currentProject'));

        $this->tpl->setNotification($this->language->__('notification.idea_board_created'), 'success', 'idea_board_created');
        session(['currentIdeaCanvas' => $currentCanvasId]);

        return Frontcontroller::redirect(BASE_URL.'/ideas/showBoards/');
    }

    /**
     * Handles editing an idea board title.
     */
    private function handleEditCanvas(int &$currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        $currentCanvasId = $this->ideaService->updateBoard($currentCanvasId, $_POST['canvastitle']);

        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success', 'idea_board_edited');

        return $this->tpl->display('canvas.boardDialog');
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(int $currentCanvasId, array $allCanvas): void
    {
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasLabels', $this->ideaService->getBoardLabels());
        $this->tpl->assign('allCanvas', $allCanvas);
        $this->tpl->assign('canvasItems', $this->ideaService->getBoardItems($currentCanvasId));
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));
    }
}
