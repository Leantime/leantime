<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Menu\Services\Menu;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Reports\Services\Reports as ReportService;
    use Leantime\Domain\Comments\Services\Comments as CommentService;

    /**
     *
     */
    class ShowMy extends Controller
    {
        private ProjectService $projectService;
        private TicketService $ticketService;
        private ReportService $reportService;
        private CommentService $commentService;
        private ClientRepository $clientRepo;

        private Menu $menuService;

        /**
         * @param ProjectService   $projectService
         * @param TicketService    $ticketService
         * @param ReportService    $reportService
         * @param CommentService   $commentService
         * @param ClientRepository $clientRepo
         * @return void
         */
        public function init(
            ProjectService $projectService,
            TicketService $ticketService,
            ReportService $reportService,
            CommentService $commentService,
            ClientRepository $clientRepo,
            Menu $menuService
        ): void {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->reportService = $reportService;
            $this->commentService = $commentService;
            $this->clientRepo = $clientRepo;
            $this->menuService = $menuService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function get()
        {

            $clientId = "";
            $currentClientName = "";

            if (isset($_GET['client']) === true && $_GET['client'] != '') {
                $clientId = (int)$_GET['client'];
                $currentClient = $this->clientRepo->getClient($clientId);
                if (is_array($currentClient) && count($currentClient) > 0) {
                    $currentClientName = $currentClient['name'];
                }
            }

            $allprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
            $clients = array();

            $projectResults = array();
            $i = 0;

            if (is_array($allprojects)) {
                foreach ($allprojects as $project) {
                    if (!array_key_exists($project["clientId"], $clients)) {
                        $clients[$project["clientId"]] = array("name" => $project['clientName'], "id" => $project["clientId"]);
                    }

                    if ($clientId == "" || $project["clientId"] == $clientId) {
                        $projectResults[$i] = $project;
                        $projectResults[$i]['progress'] = $this->projectService->getProjectProgress($project['id']);

                        $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);

                        $projectResults[$i]['milestones'] = $allProjectMilestones;
                        $projectComment = $this->commentService->getComments("project", $project['id']);

                        if (is_array($projectComment) && count($projectComment) > 0) {
                            $projectResults[$i]['lastUpdate'] = $projectComment[0];
                        } else {
                            $projectResults[$i]['lastUpdate'] = false;
                        }

                        //$fullReport = $this->reportService->getRealtimeReport($project['id'], "");

                        //$projectResults[$i]['report'] = $fullReport;

                        $i++;
                    }
                }
            }

            $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();

            $this->tpl->assign("projectTypeAvatars", $projectTypeAvatars);
            $this->tpl->assign("currentClientName", $currentClientName);
            $this->tpl->assign("currentClient", $clientId);
            $this->tpl->assign("clients", $clients);
            $this->tpl->assign("allProjects", $projectResults);
            return $this->tpl->display('projects.projectHub');
        }
    }
}
