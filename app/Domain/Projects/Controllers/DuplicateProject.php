<?php

namespace Leantime\Domain\Projects\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Symfony\Component\HttpFoundation\Response;

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
         * @return Response
         * @throws \Exception
         */
        public function get(): Response
        {
            if (
                ! Auth::userIsAtLeast(Roles::$manager)
                || ! isset($_GET['id'])
            ) {
                return $this->tpl->displayPartial('errors.error403', responseCode: 403);
            }

            $id = (int)($_GET['id']);
            $project = $this->projectService->getProject($id);

            $this->tpl->assign('allClients', $this->clientRepo->getAll());
            $this->tpl->assign("project", $project);

            return $this->tpl->displayPartial('projects.duplicateProject');
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {

            //Only admins
            if (Auth::userIsAtLeast(Roles::$manager)) {
                $id = (int)($_GET['id']);
                $projectName = $params['projectName'];
                $startDate = format(value: $_POST['startDate'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime();
                $clientId = (int) $params['clientId'];
                $assignSameUsers = false;

                if (isset($params['assignSameUsers'])) {
                    $assignSameUsers = true;
                }

                $result = $this->projectService->duplicateProject($id, $clientId, $projectName, $startDate, $assignSameUsers);

                $this->tpl->setNotification(sprintf($this->language->__("notifications.project_copied_successfully"), BASE_URL . "/projects/changeCurrentProject/" . $result), 'success');

                return Frontcontroller::redirect(BASE_URL . "/projects/duplicateProject/" . $id);
            } else {
                return $this->tpl->displayPartial('errors.error403');
            }
        }
    }
}
