<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showBoards
    {


        public function __construct()
        {

            $this->tpl = new core\template();
        }
        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $ideaRepo = new repositories\ideas();

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

                    $tpl->setNotification('NEW_CANVAS_ADDED', 'success');

                    $mailer = new core\mailer();
                    $projectService = new services\projects();
                    $users = $projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject("A new idea board was created in one of your projects");

                    $actual_link = "".CURRENT."";
                    $mailer->setHtml("A new idea board was created by " . $_SESSION["userdata"]["name"] . ": <a href='" . $actual_link . "'>" . $values['title'] . "</a>.<br />");
                    $mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    $_SESSION['currentIdeaCanvas'] = $currentCanvasId;
                    header("Location:".BASE_URL."/ideas/showBoards/");

                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            //Edit Canvas
            if (isset($_POST["editCanvas"]) === true && $currentCanvasId > 0) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                    $currentCanvasId = $ideaRepo->updateCanvas($values);

                    $tpl->setNotification("Board edited", "success");
                    $tpl->redirect(BASE_URL."/ideas/showBoards/");


                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            //Add Canvas Item
            if (isset($_POST["addItem"]) === true) {

                if (isset($_POST['description']) === true) {

                    $currentCanvasId = (int)$_SESSION['currentIdeaCanvas'];

                    $values = array(
                        "box" => $_POST['box'],
                        "author" => $_SESSION['userdata']["id"],
                        "description" => $_POST['description'],
                        "status" => "",
                        "assumptions" =>"",
                        "data" => "",
                        "conclusion" => $_POST['conclusion'],
                        "canvasId" => $currentCanvasId
                    );

                    $ideaRepo->addCanvasItem($values);

                    $_SESSION["msg"] = "NEW_CANVAS_ITEM_ADDED";
                    $_SESSION["msgT"] = "success";



                    header("Location:".BASE_URL."/ideas/showBoards/" . $currentCanvasId);

                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }
            }

            if (isset($_POST["editItem"]) === true) {

                if (isset($_POST['description']) === true) {

                    $currentCanvasId = (int)$_SESSION['currentIdeaCanvas'];

                    $values = array(
                        "box" => $_POST['box'],
                        "author" => $_SESSION['userdata']["id"],
                        "description" => $_POST['description'],
                        "status" => "",
                        "assumptions" =>"",
                        "data" => "",
                        "conclusion" => $_POST['conclusion'],
                        "itemId" => $_POST['itemId'],
                        "canvasId" => $currentCanvasId
                    );

                    $ideaRepo->editCanvasItem($values);

                    $_SESSION["msg"] = "NEW_CANVAS_ITEM_ADDED";
                    $_SESSION["msgT"] = "success";
                    header("Location:".BASE_URL."/ideas/showBoards/" . $currentCanvasId);

                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            $tpl->assign('currentCanvas', $currentCanvasId);

            $tpl->assign('allCanvas', $allCanvas);
            $tpl->assign('canvasItems', $ideaRepo->getCanvasItemsById($currentCanvasId));


            if (isset($_GET["raw"]) === false) {
                $tpl->display('ideas.showBoards');
            }
        }

    }

}


