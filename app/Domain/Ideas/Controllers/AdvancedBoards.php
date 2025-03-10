<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class AdvancedBoards extends Controller
{
    private ProjectService $projectService;

    private IdeaRepository $ideaRepo;

    private IdeaService $ideaService;

    /**
     * init - initialize private variables
     */
    public function init(
        IdeaRepository $ideaRepo,
        ProjectService $projectService
    ) {
        $this->ideaRepo = $ideaRepo;
        $this->projectService = $projectService;
        $this->ideaService = new IdeaService($ideaRepo);

        session(['lastPage' => CURRENT_URL]);
        session(['lastIdeaView' => 'kanban']);
    }

    public function get($params): Response
    {

        $allCanvas = $this->ideaRepo->getAllCanvas(session('currentProject'));

        if (session()->exists('currentIdeaCanvas')) {
            $currentCanvasId = session('currentIdeaCanvas');
        } else {
            $currentCanvasId = -1;
            session(['currentIdeaCanvas' => '']);
        }

        if (count($allCanvas) > 0 && session('currentIdeaCanvas') == '') {
            $currentCanvasId = $allCanvas[0]->id;
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        if (isset($params['id']) === true) {
            $currentCanvasId = (int) $params['id'];
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        if (isset($params['searchCanvas']) === true) {
            $currentCanvasId = (int) $params['searchCanvas'];
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        $this->prepareViewData($currentCanvasId, $allCanvas);

        // if (isset($params["raw"]) === false) {
        //     return $this->tpl->display('ideas.advancedBoards');
        // }
        return $this->tpl->display('ideas.advancedBoards');
    }

    public function post($params): Response
    {
        if (isset($params['searchCanvas'])) {
            $currentCanvasId = (int) $params['searchCanvas'];
            session(['currentIdeaCanvas' => $currentCanvasId]);
        }

        if (isset($params['newCanvas'])) {
            $result = $this->ideaService->createNewCanvas($params);
            if ($result['success']) {
                $this->tpl->setNotification($this->language->__('notification.idea_board_created'), 'success', 'ideaboard_created');

                return Frontcontroller::redirect(BASE_URL.'/ideas/advancedBoards/');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        if (isset($params['editCanvas']) && $currentCanvasId > 0) {
            $result = $this->ideaService->editCanvas($params, $currentCanvasId);
            if ($result['success']) {
                $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success', 'ideaboard_edited');

                return Frontcontroller::redirect(BASE_URL.'/ideas/advancedBoards/');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        $this->prepareViewData($currentCanvasId);

        return $this->tpl->display('ideas.advancedBoards');
        // if (!isset($params["raw"])) {
        // }
    }

    private function prepareViewData($currentCanvasId, $allCanvas = null)
    {
        if ($allCanvas === null) {
            $allCanvas = $this->ideaRepo->getAllCanvas(session('currentProject'));
        }

        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));
        $this->tpl->assign('allCanvas', $allCanvas);
        $this->tpl->assign('canvasItems', $this->ideaRepo->getCanvasItemsById($currentCanvasId));
        $this->tpl->assign('canvasLabels', $this->ideaRepo->getCanvasLabels());
    }

}
