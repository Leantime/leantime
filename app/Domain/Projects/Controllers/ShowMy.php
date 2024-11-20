<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Leantime\Domain\Menu\Services\Menu;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;

    class ShowMy extends Controller
    {
        private ProjectService $projectService;
        
        private ClientService $clientService;

        private Menu $menuService;

        /**
         * @param  TicketService  $ticketService
         * @param  ReportService  $reportService
         * @param  CommentService  $commentService
         */
        public function init(
            ProjectService $projectService,
            ClientService $clientService,
            Menu $menuService
        ): void {
            $this->projectService = $projectService;
            $this->clientService = $clientService;
            $this->menuService = $menuService;
        }

        /**
         * run - display template and edit data
         */
        public function get()
        {

            $clientId = '';
            $currentClientName = '';

            if (isset($_GET['client']) === true && $_GET['client'] != '') {
                $clientId = (int) $_GET['client'];
                $currentClient = $this->clientService->get($clientId);
                if (is_array($currentClient) && count($currentClient) > 0) {
                    $currentClientName = $currentClient['name'];
                }
            }

            $allprojects = $this->projectService->getProjectsAssignedToUser(session('userdata.id'), 'open');
            $clients = [];

            $projectResults = [];
            $i = 0;

            if (is_array($allprojects)) {
                foreach ($allprojects as $project) {

                    if (! array_key_exists($project['clientId'], $clients)) {
                        $clients[$project['clientId']] = ['name' => $project['clientName'], 'id' => $project['clientId']];
                    }

                    if ($clientId == '' || $project['clientId'] == $clientId) {
                        $projectResults[$i] = $project;
                        $i++;
                    }
                }
            }

            $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();

            $this->tpl->assign('projectTypeAvatars', $projectTypeAvatars);
            $this->tpl->assign('currentClientName', $currentClientName);
            $this->tpl->assign('currentClient', $clientId);
            $this->tpl->assign('clients', $clients);
            $this->tpl->assign('allProjects', $projectResults);

            return $this->tpl->display('projects.projectHub');
        }
    }
}
