<?php

namespace Leantime\Domain\Auth\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    class LoginInfo extends Controller
    {
        private UserRepository $userRepo;
        private UserService $userService;

        private AuthService $authService;

        public function init(
            UserRepository $userRepo,
            UserService $userService,
            AuthService $authService
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
                FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
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
