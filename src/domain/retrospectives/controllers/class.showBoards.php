<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showBoards extends controller
    {

        private $retroRepo;
        private $projectService;

        /**
         * init - initialze private variables
         *
         * @access public
         */
        private function init()
        {

            $this->retroRepo = new repositories\retrospectives();
            $this->projectService = new services\projects();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allCanvas = $this->retroRepo->getAllCanvas($_SESSION['currentProject']);

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
                    $currentCanvasId = $this->retroRepo->addCanvas($values);
                    $allCanvas = $this->retroRepo->getAllCanvas($_SESSION['currentProject']);

                    $this->tpl->setNotification("New Board added", "success");
                    $_SESSION['currentRetroCanvas'] = $currentCanvasId;
                    $this->tpl->redirect(BASE_URL."/retrospectives/showBoards/");


                } else {
                    $this->tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $this->retroRepo->updateCanvas($values);

                    $this->tpl->setNotification("Board edited", "success");
                    $this->tpl->redirect(BASE_URL."/retrospectives/showBoards/");


                } else {
                    $this->tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            $this->tpl->assign('currentCanvas', $currentCanvasId);

            $this->tpl->assign('allCanvas', $allCanvas);
            $this->tpl->assign('canvasItems', $this->retroRepo->getCanvasItemsById($currentCanvasId));
            $this->tpl->assign('canvasLabels', $this->retroRepo->canvasTypes);
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));

            if (isset($_GET["raw"]) === false) {
                $this->tpl->display('retrospectives.showBoards');
            }
        }

    }

}


