<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class AdvancedBoards extends Controller
    {
        private ProjectService $projectService;
        private IdeaRepository $ideaRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            IdeaRepository $ideaRepo,
            ProjectService $projectService
        ) {
            $this->ideaRepo = $ideaRepo;
            $this->projectService = $projectService;

            $_SESSION['lastPage'] = CURRENT_URL;
            $_SESSION['lastIdeaView'] = "kanban";
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allCanvas = $this->ideaRepo->getAllCanvas($_SESSION['currentProject']);

            if (isset($_SESSION['currentIdeaCanvas'])) {
                $currentCanvasId = $_SESSION['currentIdeaCanvas'];
            } else {
                $currentCanvasId = -1;
                $_SESSION['currentIdeaCanvas'] = "";
            }

            if (count($allCanvas) > 0 && $_SESSION['currentIdeaCanvas'] == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
            }

            if (isset($_GET["id"]) === true) {
                $currentCanvasId = (int)$_GET["id"];
                $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
            }

            if (isset($_POST["searchCanvas"]) === true) {
                $currentCanvasId = (int)$_POST["searchCanvas"];
                $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
            }

            //Add Canvas
            if (isset($_POST["newCanvas"]) === true) {
                if (isset($_POST['canvastitle']) === true) {
                    $values = array("title" => $_POST['canvastitle'], "author" => $_SESSION['userdata']["id"], "projectId" => $_SESSION["currentProject"]);
                    $currentCanvasId = $this->ideaRepo->addCanvas($values);
                    $allCanvas = $this->ideaRepo->getAllCanvas($_SESSION['currentProject']);

                    $this->tpl->setNotification($this->language->__('notification.idea_board_created'), 'success', "ideaboard_created");

                    $mailer = app()->make(MailerCore::class);
                    $mailer->setContext('idea_board_created');
                    $users = $this->projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject($this->language->__('email_notifications.idea_board_created_subject'));
                    $message = sprintf($this->language->__('email_notifications.idea_board_created_message'), $_SESSION["userdata"]["name"], "<a href='" . CURRENT_URL . "'>" . $values['title'] . "</a>.<br />");

                    $mailer->setHtml($message);
                    //$mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    // NEW Queuing messaging system
                    $queue = app()->make(QueueRepository::class);
                    $queue->queueMessageToUsers($users, $message, $this->language->__('email_notifications.idea_board_created_subject'), $_SESSION["currentProject"]);

                    $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
                    return Frontcontroller::redirect(BASE_URL . "/ideas/advancedBoards/");
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {
                if (isset($_POST['canvastitle']) === true) {
                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $this->ideaRepo->updateCanvas($values);

                    $this->tpl->setNotification($this->language->__("notification.board_edited"), "success", "ideaboard_edited");
                    return Frontcontroller::redirect(BASE_URL . "/ideas/advancedBoards/");
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            $this->tpl->assign('currentCanvas', $currentCanvasId);

            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $this->tpl->assign('allCanvas', $allCanvas);
            $this->tpl->assign('canvasItems', $this->ideaRepo->getCanvasItemsById($currentCanvasId));
            $this->tpl->assign('canvasLabels', $this->ideaRepo->getCanvasLabels());

            if (isset($_GET["raw"]) === false) {
                return $this->tpl->display('ideas.advancedBoards');
            }
        }
    }
}
