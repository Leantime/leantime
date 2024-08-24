<?php

/**
 * Controller
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Mailer;
    use Leantime\Domain\Canvas\Services\Canvas as CanvasService;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;

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
            $repoName = app()->getNamespace() . "Domain\\goalcanvas\\Repositories\\goalcanvas";
            $this->canvasRepo = app()->make($repoName);
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));

            //Create default canvas.
            if (!$allCanvas || count($allCanvas) == 0) {
                $values = [
                    'title' => $this->language->__("label.board"),
                    'author' => session("userdata.id"),
                    'projectId' => session("currentProject"),
                ];
                $currentCanvasId = $this->canvasRepo->addCanvas($values);
                $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));
            }

            if (session()->exists("current" . strtoupper(static::CANVAS_NAME) . "Canvas")) {
                $currentCanvasId = session("current" . strtoupper(static::CANVAS_NAME) . "Canvas");
                //Ensure canvas id is in the list of currentCanvases (could be old value after project select

                $found = false;
                foreach ($allCanvas as $row) {
                    if ($currentCanvasId == $row['id']) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $currentCanvasId = -1;
                    session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => '']);
                }
            } else {
                $currentCanvasId = -1;
                session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => '']);
            }

            if (count($allCanvas) > 0 && session("current" . strtoupper(static::CANVAS_NAME) . "Canvas") == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
            }

            if (isset($_GET['id']) === true) {
                $currentCanvasId = (int)$_GET['id'];
                session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
            }

            if (isset($_REQUEST['searchCanvas']) === true) {
                $currentCanvasId = (int)$_REQUEST['searchCanvas'];
                session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
                return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
            }

            // Add Canvas
            if (isset($_POST['newCanvas'])) {
                if (isset($_POST['canvastitle']) && !empty($_POST['canvastitle'])) {
                    if (!$this->canvasRepo->existCanvas(session("currentProject"), $_POST['canvastitle'])) {
                        $values = [
                            'title' => $_POST['canvastitle'],
                            'author' => session("userdata.id"),
                            'projectId' => session("currentProject"),
                        ];
                        $currentCanvasId = $this->canvasRepo->addCanvas($values);
                        $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));

                        $mailer = app()->make(Mailer::class);
                        $users = $this->projectService->getUsersToNotify(session("currentProject"));

                        $mailer->setSubject($this->language->__('notification.board_created'));

                        $actual_link = CURRENT_URL;
                        $message = sprintf(
                            $this->language->__('email_notifications.canvas_created_message'),
                            session("userdata.name"),
                            "<a href='" . $actual_link . "'>" . $values['title'] . '</a>'
                        );
                        $mailer->setHtml($message);

                        // New queuing messaging system
                        $queue = app()->make(QueueRepo::class);
                        $queue->queueMessageToUsers(
                            $users,
                            $message,
                            $this->language->__('notification.board_created'),
                            session("currentProject")
                        );

                        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success');

                        session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            // Edit Canvas
            if (isset($_POST['editCanvas']) && $currentCanvasId > 0) {
                if (isset($_POST['canvastitle']) && !empty($_POST['canvastitle'])) {
                    if (!$this->canvasRepo->existCanvas(session("currentProject"), $_POST['canvastitle'])) {
                        $values = array('title' => $_POST['canvastitle'], 'id' => $currentCanvasId);
                        $currentCanvasId = $this->canvasRepo->updateCanvas($values);

                        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');
                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            // Clone canvas
            if (isset($_POST['cloneCanvas']) && $currentCanvasId > 0) {
                if (isset($_POST['canvastitle']) && !empty($_POST['canvastitle'])) {
                    if (!$this->canvasRepo->existCanvas(session("currentProject"), $_POST['canvastitle'])) {
                        $currentCanvasId = $this->canvasRepo->copyCanvas(
                            session("currentProject"),
                            $currentCanvasId,
                            session("userdata.id"),
                            $_POST['canvastitle']
                        );
                        $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));

                        $this->tpl->setNotification($this->language->__('notification.board_copied'), 'success');

                        session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            // Merge canvas
            if (isset($_POST['mergeCanvas']) && $currentCanvasId > 0) {
                if (isset($_POST['canvasid']) && $_POST['canvasid'] > 0) {
                    $status = $this->canvasRepo->mergeCanvas($currentCanvasId, $_POST['canvasid']);

                    if ($status) {
                        $this->tpl->setNotification($this->language->__('notification.board_merged'), 'success');
                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.merge_error'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.internal_error'), 'error');
                }
            }

            // Import canvas
            if (isset($_POST['importCanvas'])) {
                if (isset($_FILES['canvasfile']) && $_FILES['canvasfile']['error'] === 0) {
                    $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.') . '.xml';

                    $status = move_uploaded_file($_FILES['canvasfile']['tmp_name'], $uploadfile);
                    if ($status) {
                        $services = app()->make(CanvasService::class);
                        $importCanvasId = $services->import(
                            $uploadfile,
                            static::CANVAS_NAME . 'canvas',
                            projectId: session("currentProject"),
                            authorId: session("userdata.id")
                        );
                        unlink($uploadfile);

                        if ($importCanvasId !== false) {
                            $currentCanvasId = $importCanvasId;
                            $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));
                            session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);

                            $mailer = app()->make(Mailer::class);
                            $users = $this->projectService->getUsersToNotify(session("currentProject"));
                            $canvas = $this->canvasRepo->getSingleCanvas($currentCanvasId);
                            $mailer->setSubject($this->language->__('notification.board_imported'));

                            $actual_link = CURRENT_URL;
                            $message = sprintf(
                                $this->language->__('email_notifications.canvas_imported_message'),
                                session("userdata.name"),
                                "<a href='" . $actual_link . "'>" . $canvas[0]['title'] . '</a>'
                            );
                            $mailer->setHtml($message);

                            // New queuing messaging system
                            $queue = app()->make(QueueRepo::class);
                            $queue->queueMessageToUsers(
                                $users,
                                $message,
                                $this->language->__('notification.board_imported'),
                                session("currentProject")
                            );

                            $this->tpl->setNotification($this->language->__('notification.board_imported'), 'success');
                            return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
                        } else {
                            $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');
                    }
                }
            }

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

            if (!isset($_GET['raw'])) {
                return $this->tpl->display(static::CANVAS_NAME . 'canvas.showCanvas');
            }
        }
    }

}
