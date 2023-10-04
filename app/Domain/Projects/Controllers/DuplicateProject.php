<?php

namespace Leantime\Domain\Projects\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class DuplicateProject extends Controller
    {
        private ProjectService $projectService;
        private ClientRepository $clientRepo;
        private ProjectRepository $projectRepo;

        /**
         * @param ProjectRepository $projectRepo
         * @param ClientRepository  $clientRepo
         * @param ProjectService    $projectService
         * @return void
         */
        public function init(
            ProjectRepository $projectRepo,
            ClientRepository $clientRepo,
            ProjectService $projectService
        ): void {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            $this->projectRepo = $projectRepo;
            $this->clientRepo = $clientRepo;
            $this->projectService = $projectService;
        }

        /**
         * @return void
         * @throws \Exception
         */
        public function get(): void
        {

            //Only admins
            if (Auth::userIsAtLeast(Roles::$manager)) {
                if (isset($_GET['id']) === true) {
                    $id = (int)($_GET['id']);
                    $project = $this->projectService->getProject($id);


                    $this->tpl->assign('allClients', $this->clientRepo->getAll());


                    $this->tpl->assign("project", $project);
                    $this->tpl->displayPartial('projects.duplicateProject');
                } else {
                    $this->tpl->displayPartial('errors.error403');
                }
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }

        /**
         * @param $params
         * @return void
         */
        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        public function post($params): void
        {

            //Only admins
            if (Auth::userIsAtLeast(Roles::$manager)) {
                $id = (int)($_GET['id']);
                $projectName = $params['projectName'];
                $startDate = $this->language->getISODateString($params['startDate']);
                $clientId = (int) $params['clientId'];
                $assignSameUsers = false;

                if (isset($params['assignSameUsers'])) {
                    $assignSameUsers = true;
                }

                $result = $this->projectService->duplicateProject($id, $clientId, $projectName, $startDate, $assignSameUsers);

                $this->tpl->setNotification(sprintf($this->language->__("notifications.project_copied_successfully"), BASE_URL . "/projects/changeCurrentProject/" . $result), 'success');

                $this->tpl->redirect(BASE_URL . "/projects/duplicateProject/" . $id);
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }

}
