<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class simpleCanvas extends controller
    {

        private $leancanvasRepo;
        private $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->leancanvasRepo = new repositories\leancanvas();
            $this->projectService = new services\projects();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allCanvas = $this->leancanvasRepo->getAllCanvas($_SESSION['currentProject']);

            if(isset($_SESSION['currentLeanCanvas'])) {
                $currentCanvasId = $_SESSION['currentLeanCanvas'];
            }else{
                $currentCanvasId = -1;
                $_SESSION['currentLeanCanvas'] = "";
            }

            if (count($allCanvas) > 0 && $_SESSION['currentLeanCanvas'] == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                $_SESSION['currentLeanCanvas'] = $currentCanvasId;
            }

            if (isset($_GET["id"]) === true) {
                $currentCanvasId = (int)$_GET["id"];
                $_SESSION['currentLeanCanvas'] = $currentCanvasId;
            }

            if (isset($_POST["searchCanvas"]) === true) {
                $currentCanvasId = (int)$_POST["searchCanvas"];
                $_SESSION['currentLeanCanvas'] = $currentCanvasId;
            }

            //Add Canvas
            if (isset($_POST["newCanvas"]) === true) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "author" => $_SESSION['userdata']["id"], "projectId" => $_SESSION["currentProject"]);
                    $currentCanvasId = $this->leancanvasRepo->addCanvas($values);
                    $allCanvas = $this->leancanvasRepo->getAllCanvas($_SESSION['currentProject']);

                    $mailer = new core\mailer();
                    $mailer->setContext('new_canvas_created');
                    $users = $this->projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject($this->language->__("notifications.new_canvas_created"));

                    $actual_link = CURRENT_URL;
                    $message = sprintf($this->language->__("email_notifications.canvas_created_message"),$_SESSION["userdata"]["name"], "<a href='" . $actual_link . "'>" . $values['title'] . "</a>");
                    $mailer->setHtml($message);
                    //$mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    // NEW Queuing messaging system
                    $queue = new repositories\queue();
                    $queue->queueMessageToUsers($users, $message, $this->language->__("notifications.new_canvas_created"), $_SESSION["currentProject"]);


                    $this->tpl->setNotification($this->language->__("notifications.new_canvas_created"), 'success');

                    $_SESSION['currentLeanCanvas'] = $currentCanvasId;
                    $this->tpl->redirect(BASE_URL."/leancanvas/simpleCanvas/");

                } else {
                    $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');
                }

            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $this->leancanvasRepo->updateCanvas($values);

                    $this->tpl->setNotification($this->language->__("notification.board_edited"), "success");
                    $this->tpl->redirect(BASE_URL."/leancanvas/simpleCanvas/");


                } else {
                    $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');
                }

            }

            $this->tpl->assign('currentCanvas', $currentCanvasId);
            $this->tpl->assign('statusLabels', $this->leancanvasRepo->getStatusLabels());
            $this->tpl->assign('canvasLabels', $this->leancanvasRepo->canvasTypes);
            $this->tpl->assign('allCanvas', $allCanvas);
            $this->tpl->assign('canvasItems', $this->leancanvasRepo->getCanvasItemsById($currentCanvasId));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));


            if (isset($_GET["raw"]) === false) {
                $this->tpl->display('leancanvas.simpleCanvas');
            }
        }

    }

}
