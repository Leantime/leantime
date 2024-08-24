<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;

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
                if (!session()->exists("showClosedProjects")) {
                    session(["showClosedProjects" => false]);
                }

                if (isset($_POST['hideClosedProjects'])) {
                    session(["showClosedProjects" => false]);
                }

                if (isset($_POST['showClosedProjects'])) {
                    session(["showClosedProjects" => true]);
                }

                $this->tpl->assign('role', session("userdata.role"));

                if (Auth::userIsAtLeast(Roles::$admin)) {
                    $this->tpl->assign('allProjects', $this->projectRepo->getAll(session("showClosedProjects")));
                } else {
                    $this->tpl->assign('allProjects', $this->projectService->getClientManagerProjects(session("userdata.id"), session("userdata.clientId")));
                }
                $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());

                $this->tpl->assign('showClosedProjects', session("showClosedProjects"));

                return $this->tpl->display('projects.showAll');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
