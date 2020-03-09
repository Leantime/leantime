<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class simpleCanvas
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $leancanvasRepo = new repositories\leancanvas();
            $projectService = new services\projects();
            $language = new core\language();

            $allCanvas = $leancanvasRepo->getAllCanvas($_SESSION['currentProject']);

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
                    $currentCanvasId = $leancanvasRepo->addCanvas($values);
                    $allCanvas = $leancanvasRepo->getAllCanvas($_SESSION['currentProject']);

                    $mailer = new core\mailer();
                    $projectService = new services\projects();
                    $users = $projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject($language->__("notifications.new_canvas_created"));

                    $actual_link = CURRENT_URL;
                    $message = sprintf($language->__("email_notifications.canvas_created_message"),$_SESSION["userdata"]["name"], "<a href='" . $actual_link . "'>" . $values['title'] . "</a>");
                    $mailer->setHtml($message);
                    $mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    $tpl->setNotification($language->__("notifications.new_canvas_created"), 'success');

                    $_SESSION['currentLeanCanvas'] = $currentCanvasId;
                    $tpl->redirect(BASE_URL."/leancanvas/simpleCanvas/");

                } else {
                    $tpl->setNotification($language->__("notification.please_enter_title"), 'error');
                }

            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $leancanvasRepo->updateCanvas($values);

                    $tpl->setNotification($language->__("notification.board_edited"), "success");
                    $tpl->redirect(BASE_URL."/leancanvas/simpleCanvas/");


                } else {
                    $tpl->setNotification($language->__("notification.please_enter_title"), 'error');
                }

            }

            $tpl->assign('currentCanvas', $currentCanvasId);
            $tpl->assign('statusLabels', $leancanvasRepo->getStatusLabels());
            $tpl->assign('canvasLabels', $leancanvasRepo->canvasTypes);
            $tpl->assign('allCanvas', $allCanvas);
            $tpl->assign('canvasItems', $leancanvasRepo->getCanvasItemsById($currentCanvasId));
            $tpl->assign('users', $projectService->getUsersAssignedToProject($_SESSION["currentProject"]));


            if (isset($_GET["raw"]) === false) {
                $tpl->display('leancanvas.simpleCanvas');
            }
        }

    }

}