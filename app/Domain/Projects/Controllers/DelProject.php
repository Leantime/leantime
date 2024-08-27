<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;

    /**
     *
     */
    class DelProject extends Controller
    {
        private ProjectRepository $projectRepo;
        private ProjectService $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(ProjectRepository $projectRepo, ProjectService $projectService)
        {
            $this->projectRepo = $projectRepo;
            $this->projectService = $projectService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$manager)) {
                if (isset($_GET['id']) === true) {
                    $id = (int)($_GET['id']);

                    if ($this->projectRepo->hasTickets($id)) {
                        $this->tpl->setNotification($this->language->__("notification.project_has_tasks"), "info");
                    }

                    if (isset($_POST['del']) === true) {
                        $this->projectRepo->deleteProject($id);
                        $this->projectRepo->deleteAllUserRelations($id);

                        $this->projectService->resetCurrentProject();
                        $this->projectService->setCurrentProject();

                        $this->tpl->setNotification($this->language->__("notification.project_deleted"), "success");
                        return Frontcontroller::redirect(BASE_URL . "/projects/showAll");
                    }

                    //Assign vars
                    $project = $this->projectRepo->getProject($id);
                    if ($project === false) {
                        return FrontcontrollerCore::redirect(BASE_URL . "/errors/error404");
                    }

                    $this->tpl->assign('project', $project);

                    return $this->tpl->display('projects.delProject');
                } else {
                    return $this->tpl->display('errors.error403', responseCode: 403);
                }
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
