<?php

/**
 * showCanvas class - Generic canvas controller
 */

namespace Leantime\Domain\Canvas\Controllers {

    use Illuminate\Support\Str;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;

    /**
     *
     */
    class BoardDialog extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private ProjectService $projectService;
        private object $canvasRepo;

        /**
         * init - initialize private variables
         */
        public function init(ProjectService $projectService)
        {
            $this->projectService = $projectService;
            $canvasName = Str::studly(static::CANVAS_NAME) . 'canvas';
            $repoName = app()->getNamespace() . "Domain\\$canvasName\\Repositories\\$canvasName";
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
            $currentCanvasId = '';
            $canvasTitle = '';

            if (isset($_GET['id']) === true) {
                $currentCanvasId = (int)$_GET['id'];
                $singleCanvas = $this->canvasRepo->getSingleCanvas($currentCanvasId);
                $canvasTitle = $singleCanvas[0]["title"] ?? "";
                session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
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

                        $mailer = app()->make(MailerCore::class);
                        $this->projectService = app()->make(ProjectService::class);
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
                        $queue = app()->make(QueueRepository::class);
                        $queue->queueMessageToUsers(
                            $users,
                            $message,
                            $this->language->__('notification.board_created'),
                            session("currentProject")
                        );

                        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success', static::CANVAS_NAME . "board_created");

                        session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $currentCanvasId]);
                        return Frontcontroller::redirect(BASE_URL . '/'.static::CANVAS_NAME.'canvas/boardDialog/'.$currentCanvasId);
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
                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/boardDialog/'.$values['id']);
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            $this->tpl->assign('currentCanvas', $currentCanvasId);
            $this->tpl->assign('canvasName', static::CANVAS_NAME);
            $this->tpl->assign('canvasTitle',$canvasTitle);




            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session("currentProject")));

            if (!isset($_GET['raw'])) {
                return $this->tpl->displayPartial('canvas.boardDialog');
            }
        }
    }

}
