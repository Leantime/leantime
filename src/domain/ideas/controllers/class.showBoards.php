<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showBoards extends controller
    {

        private $ideaRepo;
        private $projectService;

        /**
         * init - initialize private variables
         *
         * @access private
         */
        public function init()
        {

            $this->ideaRepo = new repositories\ideas();
            $this->projectService = new services\projects();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allCanvas = $this->ideaRepo->getAllCanvas($_SESSION['currentProject']);

            if(isset($_SESSION['currentIdeaCanvas'])) {
                $currentCanvasId = $_SESSION['currentIdeaCanvas'];
            }else{
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

                    $this->tpl->setNotification($this->language->__('notification.idea_board_created'), 'success');

                    $mailer = new core\mailer();
                    $mailer->setContext('idea_board_created');
                    $this->projectService = new services\projects();
                    $users = $this->projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject($this->language->__('email_notifications.idea_board_created_subject'));
                    $message = sprintf($this->language->__('email_notifications.idea_board_created_message'), $_SESSION["userdata"]["name"], "<a href='" . CURRENT_URL . "'>" . $values['title'] . "</a>.<br />");

                    $mailer->setHtml($message);
                    //$mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    // NEW Queuing messaging system
                    $queue = new repositories\queue();
                    $queue->queueMessageToUsers($users, $message, $this->language->__('email_notifications.idea_board_created_subject'), $_SESSION["currentProject"]);


                    $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
                    $this->tpl->redirect(BASE_URL."/ideas/showBoards/");

                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }

            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $this->ideaRepo->updateCanvas($values);

                    $this->tpl->setNotification($this->language->__("notification.board_edited"), "success");
                    $this->tpl->redirect(BASE_URL."/ideas/showBoards/");


                } else {

                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

                }

            }

            $this->tpl->assign('currentCanvas', $currentCanvasId);
            $this->tpl->assign('canvasLabels', $this->ideaRepo->getCanvasLabels());
            $this->tpl->assign('allCanvas', $allCanvas);
            $this->tpl->assign('canvasItems', $this->ideaRepo->getCanvasItemsById($currentCanvasId));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));


            if (isset($_GET["raw"]) === false) {
                $this->tpl->display('ideas.showBoards');
            }
        }

    }

}


