<?php

/**
 * Controller
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Symfony\Component\HttpFoundation\Response;

    class Dashboard extends Controller
    {
        /**
         * Constant that must be redefined
         */
        private Goalcanvas $goalService;

        /**
         * init - initialize private variables
         */
        public function init(
            Goalcanvas $goalService
        ) {
            $this->goalService = $goalService;
        }

        /**
         * run - display template and edit data
         */
        public function get($params): Response
        {

            $result = $this->goalService->handleDashboardGetRequest($params);

            $this->tpl->assign('currentCanvas', $result['currentCanvasId']);
            $this->tpl->assign('goalStats', $result['goalAnalytics']);
            $this->tpl->assign('canvasIcon', $result['canvasIcon']);
            $this->tpl->assign('canvasTypes', $result['canvasTypes']);
            $this->tpl->assign('statusLabels', $result['statusLabels']);
            $this->tpl->assign('relatesLabels', $result['relatesLabels']);
            $this->tpl->assign('dataLabels', $result['dataLabels']);
            $this->tpl->assign('disclaimer', $result['disclaimer']);
            $this->tpl->assign('allCanvas', $result['allCanvas']);
            $this->tpl->assign('canvasItems', $result['canvasItems']);
            $this->tpl->assign('users', $result['users']);

            return $this->tpl->display('goalcanvas.dashboard');
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

            if ($result['success']) {
                $this->tpl->setNotification($result['message'], 'success');

                return Frontcontroller::redirect(BASE_URL.'/goalcanvas/showCanvas/');
            } else {
                $this->tpl->setNotification($result['message'], 'error');
            }

            return $this->get($params);
        }
    }
}
