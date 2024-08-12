<?php

/**
 * Controller
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Mailer;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;
    use Leantime\Domain\Canvas\Services\Canvas as CanvasService;
    use Illuminate\Support\Str;
    use Leantime\Core\Frontcontroller;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ShowCanvas extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'goal';

        private $canvasRepo;
        private Projects $projectService;
        private Goalcanvas $goalService;

        /**
         * init - initialize private variables
         */
        public function init(Projects $projectService, Goalcanvas $goalService)
        {
            $this->projectService = $projectService;
            $this->goalService = $goalService;
            $canvasName = Str::studly(static::CANVAS_NAME) . 'canvas';
            $repoName = app()->getNamespace() . "Domain\\$canvasName\\Repositories\\$canvasName";
            $this->canvasRepo = app()->make($repoName);
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */



        public function get($params): Response
        {
            $currentCanvasId = $this->goalService->getCurrentCanvasId($params);
            $allCanvas = $this->goalService->getAllCanvas();
        
            $this->tpl->assign('currentCanvas', $currentCanvasId);
            $this->tpl->assign('canvasIcon', $this->canvasRepo->getIcon());
            $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
            $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());
            $this->tpl->assign('relatesLabels', $this->canvasRepo->getRelatesLabels());
            $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());
            $this->tpl->assign('disclaimer', $this->canvasRepo->getDisclaimer());
            $this->tpl->assign('allCanvas', $allCanvas);
            $this->tpl->assign('canvasItems', $this->goalService->getCanvasItemsById($currentCanvasId));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session("currentProject")));
        
            return $this->tpl->display(static::CANVAS_NAME . 'canvas.showCanvas');
        }

        public function post($params): Response
        {
            $action = $_POST['action'] ?? '';
            $result = null;
        
            switch ($action) {
                case 'newCanvas':
                    $result = $this->goalService->createNewCanvas($_POST['canvastitle'] ?? '');
                    break;
                case 'editCanvas':
                    $result = $this->goalService->editCanvas($_POST['canvastitle'] ?? '', $_POST['canvasid'] ?? -1);
                    break;
                case 'cloneCanvas':
                    $result = $this->goalService->cloneCanvas($_POST['canvastitle'] ?? '', $_POST['canvasid'] ?? -1);
                    break;
                case 'mergeCanvas':
                    $result = $this->goalService->mergeCanvas($_POST['canvasid'] ?? -1, $_POST['mergeCanvasId'] ?? -1);
                    break;
                case 'importCanvas':
                    $result = $this->goalService->importCanvas($_FILES['canvasfile'] ?? null);
                    break;
            }
        
            if ($result['success']) {
                $this->tpl->setNotification($result['message'], 'success');
                return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
            } else {
                $this->tpl->setNotification($result['message'], 'error');
            }
        
            return $this->get($params);
        }
    }

}
