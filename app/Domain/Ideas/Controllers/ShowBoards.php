<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;

    /**
     *
     */
    class ShowBoards extends Controller
    {
        private IdeaRepository $ideaRepo;
        private ProjectService $projectService;

        /**
         * init - initialize private variables
         *
         * @access private
         */
        public function init(IdeaRepository $ideaRepo, ProjectService $projectService)
        {
            $this->ideaRepo = $ideaRepo;
            $this->projectService = $projectService;

            session(["lastPage" => CURRENT_URL]);
            session(["lastIdeaView" => "board"]);
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allCanvas = $this->ideaRepo->getAllCanvas(session("currentProject"));
            if (!$allCanvas || count($allCanvas) == 0) {
                $values = [
                    'title' => $this->language->__("label.board"),
                    'author' => session("userdata.id"),
                    'projectId' => session("currentProject"),
                ];
                $currentCanvasId = $this->ideaRepo->addCanvas($values);
                $allCanvas = $this->ideaRepo->getAllCanvas(session("currentProject"));
            }

            if (session()->exists("currentIdeaCanvas")) {
                $currentCanvasId = session("currentIdeaCanvas");
            } else {
                $currentCanvasId = -1;
                session(["currentIdeaCanvas" => ""]);
            }

            if (count($allCanvas) > 0 && session("currentIdeaCanvas") == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                session(["currentIdeaCanvas" => $currentCanvasId]);
            }

            if (isset($_GET["id"]) === true) {
                $currentCanvasId = (int)$_GET["id"];
                session(["currentIdeaCanvas" => $currentCanvasId]);
            }

            if (isset($_POST["searchCanvas"]) === true) {
                $currentCanvasId = (int)$_POST["searchCanvas"];
                session(["currentIdeaCanvas" => $currentCanvasId]);
            }

            //Add Canvas
            if (isset($_POST["newCanvas"]) === true) {
                if (isset($_POST['canvastitle']) === true) {
                    $values = array("title" => $_POST['canvastitle'], "author" => session("userdata.id"), "projectId" => session("currentProject"));
                    $currentCanvasId = $this->ideaRepo->addCanvas($values);
                    $allCanvas = $this->ideaRepo->getAllCanvas(session("currentProject"));

                    $this->tpl->setNotification($this->language->__('notification.idea_board_created'), 'success', 'idea_board_created');

                    $mailer = app()->make(MailerCore::class);
                    $mailer->setContext('idea_board_created');
                    $users = $this->projectService->getUsersToNotify(session("currentProject"));

                    $mailer->setSubject($this->language->__('email_notifications.idea_board_created_subject'));
                    $message = sprintf($this->language->__('email_notifications.idea_board_created_message'), session("userdata.name"), "<a href='" . CURRENT_URL . "'>" . $values['title'] . "</a>.<br />");

                    $mailer->setHtml($message);
                    //$mailer->sendMail($users, session("userdata.name"));

                    // NEW Queuing messaging system
                    $queue = app()->make(QueueRepository::class);
                    $queue->queueMessageToUsers($users, $message, $this->language->__('email_notifications.idea_board_created_subject'), session("currentProject"));


                    session(["currentIdeaCanvas" => $currentCanvasId]);
                    return Frontcontroller::redirect(BASE_URL . "/ideas/showBoards/");
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {
                if (isset($_POST['canvastitle']) === true) {
                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $this->ideaRepo->updateCanvas($values);

                    $this->tpl->setNotification($this->language->__("notification.board_edited"), "success", "idea_board_edited");
                    return $this->tpl->display('canvas.boardDialog');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            $this->tpl->assign('currentCanvas', $currentCanvasId);
            $this->tpl->assign('canvasLabels', $this->ideaRepo->getCanvasLabels());
            $this->tpl->assign('allCanvas', $allCanvas);
            $this->tpl->assign('canvasItems', $this->ideaRepo->getCanvasItemsById($currentCanvasId));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session("currentProject")));

            if (isset($_GET["raw"]) === false) {
                return $this->tpl->display('ideas.showBoards');
            }
        }
    }

}
