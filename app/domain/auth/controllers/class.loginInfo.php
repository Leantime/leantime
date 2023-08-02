<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class loginInfo extends controller
    {
        private repositories\users $userRepo;
        private services\users $userService;

        private services\auth $authService;

        public function init(
            repositories\users $userRepo,
            services\users $userService,
            services\auth $authService
        ) {
            $this->userRepo = $userRepo;
            $this->userService = $userService;
            $this->authService = $authService;
        }

        public function run()
        {
            $user = false;
            if (isset($_SESSION['userdata'])) {
                $user = $this->userService->getUser($_SESSION['userdata']['id']);
            }

            if ($user === false) {
                $this->authService->logout();
                core\frontcontroller::redirect(BASE_URL . "/auth/login");
            }

            $profilePicture = $this->userService->getProfilePicture($_SESSION['userdata']['id']);

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

            if (count($urlParts) > 2) {
                $urlKey =  $urlParts[1] . "/" . $urlParts[2];

                if (isset($availableModals[$urlKey])) {
                    $modal = $availableModals[$urlKey];
                } else {
                    $modal = "notfound";
                }
            } else {
                $modal = "dashboard";
            }

            $this->tpl->assign("modal", $modal);


            $this->tpl->assign("profilePicture", $profilePicture);
            $this->tpl->assign("userName", $user["firstname"]);
            $this->tpl->assign("userId", $user["id"]);
            $this->tpl->assign("userEmail", $user["username"]);
            $this->tpl->assign("profileId", $user["profileId"]);

            $this->tpl->displayPartial("auth.loginInfo");
        }
    }
}
