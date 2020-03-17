<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class advancedBoards
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $ideaRepo = new repositories\ideas();
            $projectService = new services\projects();
            $language = new core\language();

            $allCanvas = $ideaRepo->getAllCanvas($_SESSION['currentProject']);

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
                    $currentCanvasId = $ideaRepo->addCanvas($values);
                    $allCanvas = $ideaRepo->getAllCanvas($_SESSION['currentProject']);

                    $tpl->setNotification($language->__('notification.idea_board_created'), 'success');

                    $mailer = new core\mailer();
                    $projectService = new services\projects();
                    $users = $projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject($language->__('email_notifications.idea_board_created_subject'));
                    $message = sprintf($language->__('email_notifications.idea_board_created_message'), $_SESSION["userdata"]["name"], "<a href='" . CURRENT_URL . "'>" . $values['title'] . "</a>.<br />");

                    $mailer->setHtml($message);
                    $mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
                    $tpl->redirect(BASE_URL."/ideas/advancedBoards/");

                } else {
                    $tpl->setNotification($language->__('notification.please_enter_title'), 'error');
                }


            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $ideaRepo->updateCanvas($values);

                    $tpl->setNotification($language->__("notification.board_edited"), "success");
                    $tpl->redirect(BASE_URL."/ideas/advancedBoards/");


                } else {

                    $tpl->setNotification($language->__('notification.please_enter_title'), 'error');

                }

            }

            $tpl->assign('currentCanvas', $currentCanvasId);

            $tpl->assign('users', $projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $tpl->assign('allCanvas', $allCanvas);
            $tpl->assign('canvasItems', $ideaRepo->getCanvasItemsById($currentCanvasId));
            $tpl->assign('canvasLabels', $ideaRepo->getCanvasLabels());

            if (isset($_GET["raw"]) === false) {
                $tpl->display('ideas.advancedBoards');
            }
        }

    }

}


