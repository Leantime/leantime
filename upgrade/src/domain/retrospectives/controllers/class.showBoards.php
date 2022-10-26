<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showBoards
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $retroRepo = new repositories\retrospectives();
            $projectService = new services\projects();

            $allCanvas = $retroRepo->getAllCanvas($_SESSION['currentProject']);

            if(isset($_SESSION['currentRetroCanvas'])) {
                $currentCanvasId = $_SESSION['currentRetroCanvas'];
            }else{
                $currentCanvasId = -1;
                $_SESSION['currentRetroCanvas'] = "";
            }

            if (count($allCanvas) > 0 && $_SESSION['currentRetroCanvas'] == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                $_SESSION['currentRetroCanvas'] = $currentCanvasId;
            }

            if (isset($_GET["id"]) === true) {
                $currentCanvasId = (int)$_GET["id"];
                $_SESSION['currentRetroCanvas'] = $currentCanvasId;
            }

            if (isset($_POST["searchCanvas"]) === true) {
                $currentCanvasId = (int)$_POST["searchCanvas"];
                $_SESSION['currentRetroCanvas'] = $currentCanvasId;
            }

            //Add Canvas
            if (isset($_POST["newCanvas"]) === true) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "author" => $_SESSION['userdata']["id"], "projectId" => $_SESSION["currentProject"]);
                    $currentCanvasId = $retroRepo->addCanvas($values);
                    $allCanvas = $retroRepo->getAllCanvas($_SESSION['currentProject']);

                    $tpl->setNotification("New Board added", "success");
                    $_SESSION['currentRetroCanvas'] = $currentCanvasId;
                    $tpl->redirect(BASE_URL."/retrospectives/showBoards/");


                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $retroRepo->updateCanvas($values);

                    $tpl->setNotification("Board edited", "success");
                    $tpl->redirect(BASE_URL."/retrospectives/showBoards/");


                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            $tpl->assign('currentCanvas', $currentCanvasId);

            $tpl->assign('allCanvas', $allCanvas);
            $tpl->assign('canvasItems', $retroRepo->getCanvasItemsById($currentCanvasId));
            $tpl->assign('canvasLabels', $retroRepo->canvasTypes);
            $tpl->assign('users', $projectService->getUsersAssignedToProject($_SESSION["currentProject"]));

            if (isset($_GET["raw"]) === false) {
                $tpl->display('retrospectives.showBoards');
            }
        }

    }

}


