<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class ShowAll extends Controller
    {
        private ProjectRepository $projectRepo;
        private MenuRepository $menuRepo;
        private ProjectService $projectService;


        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            ProjectRepository $projectRepo,
            MenuRepository $menuRepo,
            ProjectService $projectService
        ) {
            $this->projectRepo = $projectRepo;
            $this->projectService = $projectService;
            $this->menuRepo = $menuRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            if (Auth::userIsAtLeast(Roles::$manager)) {
                if (!isset($_SESSION['showClosedProjects'])) {
                    $_SESSION['showClosedProjects'] = false;
                }

                if (isset($_POST['hideClosedProjects'])) {
                    $_SESSION['showClosedProjects'] = false;
                }

                if (isset($_POST['showClosedProjects'])) {
                    $_SESSION['showClosedProjects'] = true;
                }

                $this->tpl->assign('role', $_SESSION['userdata']['role']);

                if (Auth::userIsAtLeast(Roles::$admin)) {
                    $this->tpl->assign('allProjects', $this->projectRepo->getAll($_SESSION['showClosedProjects']));
                } else {
                    $this->tpl->assign('allProjects', $this->projectService->getClientManagerProjects($_SESSION['userdata']['id'], $_SESSION['userdata']['clientId']));
                }
                $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());

                $this->tpl->assign('showClosedProjects', $_SESSION['showClosedProjects']);

                return $this->tpl->display('projects.showAll');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
