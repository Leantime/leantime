<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\repositories;

    class loginInfo extends controller
    {

        private $userRepo;

        public function init()
        {
            $userRepo = new repositories\users();
        }

        public function run()
        {

            $profilePicture = $this->userRepo->getProfilePicture($_SESSION['userdata']['id']);

            $user = $this->userRepo->getUser($_SESSION['userdata']['id']);

            $availableModals = array(
                "tickets/showAll" => "backlog",
                "dashboard/show" => "dashboard",
                "dashboard/home" => "dashboard",
                "leancanvas/showCanvas" => "fullLeanCanvas",
                "leancanvas/simpleCanvas" => "simpleLeanCanvas",
                "ideas/showBoards" => "ideaBoard",
                "ideas/advancedBoards" => "advancedBoards",
                "tickets/roadmap" => "roadmap",
                "retroscanvas/showBoards" => "retroscanvas",
                "tickets/showKanban" => "kanban",
                "timesheets/showMy" => "mytimesheets",
                "projects/newProject" => "newProject",
                "projects/showProject" => "projectSuccess",
                "projects/showAll" => "showProjects",
                "clients/showAll" => "showClients",
            );

            $url = CURRENT_URL;

            $requestParams = explode(BASE_URL, $url);
            $urlParts = explode('/', $requestParams[1]);
            $modal = "";

            if(count($urlParts) > 2) {
                $urlKey =  $urlParts[1]."/".$urlParts[2];

                if(isset($availableModals[$urlKey])) {
                    $modal = $availableModals[$urlKey];
                }else{
                    $modal = "notfound";
                }
            }else{
                $modal = "dashboard";
            }

            $this->tpl->assign("modal", $modal);


            $this->tpl->assign("profilePicture", $profilePicture);
            $this->tpl->assign("userName", $user["firstname"]);
            $this->tpl->assign("userEmail", $user["username"]);
            $this->tpl->assign("profileId", $user["profileId"]);

            $this->tpl->displayPartial("general.loginInfo");

        }
    }
}
