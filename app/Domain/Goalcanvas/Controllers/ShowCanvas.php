<?php

/**
 * Controller
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use leantime\core\Controller\Controller;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Leantime\Domain\Projects\Services\Projects;
    use Symfony\Component\HttpFoundation\Response;


    /**
     *
     */
    class ShowCanvas extends Controller
    {
        /**
         * Constant that must be redefined
         */

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
            $repoName = app()->getNamespace() . "Domain\\goalcanvas\\Repositories\\goalcanvas";
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

            $filter['status'] = $_GET['filter_status'] ?? (session("filter_status") ?? 'all');
            session(["filter_status" => $filter['status']]);
            $filter['relates'] = $_GET['filter_relates'] ?? (session("filter_relates") ?? 'all');
            session(["filter_relates" => $filter['relates']]);

            $this->tpl->assign('filter', $filter);
        
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
        
            return $this->tpl->display('goalcanvas.showCanvas');
        }

        public function post($params): Response
        {
            $action = $_POST['action'] ?? '';
            $result = null;
        
            switch ($action) {
                case 'newCanvas':
                    try {
                        $result = $this->goalService->createNewCanvas($_POST['canvastitle'] ?? '');
                        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success');
                    } catch (\Throwable $th) {
                        $this->tpl->setNotification($th->getMessage(), 'error');
                    }
                    break;
                case 'editCanvas':
                    try {
                        //code...
                        $result = $this->goalService->editCanvas($_POST['canvastitle'] ?? '', $_POST['canvasid'] ?? -1);
                        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');
                    } catch (\Throwable $th) {
                        //throw $th;
                        $this->tpl->setNotification($th->getMessage(), 'error');
                    }

                    break;
                case 'cloneCanvas':
                    try {
                        //code...
                        $result = $this->goalService->cloneCanvas($_POST['canvastitle'] ?? '', $_POST['canvasid'] ?? -1);
                        $this->tpl->setNotification($this->language->__('notification.board_copied'), 'success');
                    } catch (\Throwable $th) {
                        //throw $th;
                        $this->tpl->setNotification($th->getMessage(), 'error');
                    }
                    break;
                case 'mergeCanvas':
                    try {
                        //code...
                        $result = $this->goalService->mergeCanvas($_POST['canvasid'] ?? -1, $_POST['mergeCanvasId'] ?? -1);
                        $this->tpl->setNotification($this->language->__('notification.board_merged'), 'success');
                    } catch (\Throwable $th) {
                        //throw $th;
                        $this->tpl->setNotification($th->getMessage(), 'error');
                    }
                    break;
                case 'importCanvas':
                    try {
                        $result = $this->goalService->importCanvas($_FILES['canvasfile'] ?? null);
                        $this->tpl->setNotification($this->language->__('notification.board_imported'), 'success');
                    } catch (\Throwable $th) {
                        $this->tpl->setNotification($th->getMessage(), 'error');

                    }
                    break;
            }
        
            return $this->get($params);
        }
    }


    

}
