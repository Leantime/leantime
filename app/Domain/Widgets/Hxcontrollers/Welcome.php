<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;

class Welcome extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'widgets::partials.welcome';

    private ProjectService $projectsService;
    private TicketService $ticketsService;
    private UserService $usersService;

    /**
     * Initializes the class by assigning the given services and setting the last page session variable.
     *
     * @param ProjectService $projectsService The project service object.
     * @param TicketService $ticketsService The ticket service object.
     * @param UserService $usersService The user service object.
     * @return void
     */
    public function init(
        ProjectService $projectsService,
        TicketService $ticketsService,
        UserService $usersService,
    ) {
        $this->projectsService = $projectsService;
        $this->ticketsService = $ticketsService;
        $this->usersService = $usersService;
        session(["lastPage" => BASE_URL . "/dashboard/home"]);
    }

    /**
     * Retrieves various data and assigns them to a template for display.
     *
     * @return void
     */
    public function get()
    {

        try {
            $reportService = app()->make(ReportService::class);
            $promise = $reportService->sendAnonymousTelemetry();
            if ($promise !== false) {
                $promise->wait();
            }
        }catch(\Exception $e) {
            report($e);
        }

        $currentUser = $this->usersService->getUser(session("userdata.id"));
        $this->tpl->assign('currentUser', $currentUser);


        //Todo: Write queries.
        $totalTickets = $this->ticketsService->simpleTicketCounter(userId: session("userdata.id"), status: "not_done");

        $closedTicketsCount = 0;
        $closedTickets = $this->ticketsService->getRecentlyCompletedTicketsByUser(session("userdata.id"), null);
        if (is_array($closedTickets)) {
            $closedTicketsCount = count($closedTickets);
        }

        $ticketsInGoals = 0;
        $goalTickets = $this->ticketsService->goalsRelatedToWork(session("userdata.id"), null);
        if (is_array($goalTickets)) {
            $ticketsInGoals = count($goalTickets);
        }

        $todayTaskCount = 0;

        $todayStart = dtHelper()->userNow()->startOfDay();
        $todayEnd = dtHelper()->userNow()->endOfDay();
        $todaysTasks = $this->ticketsService->getScheduledTasks($todayStart, $todayEnd, session("userdata.id"));
        $totalToday = count($todaysTasks['totalTasks'] ?? []);
        $doneToday = count($todaysTasks['doneTasks'] ?? []);


        $this->tpl->assign('totalTickets', $totalTickets);
        $this->tpl->assign('closedTicketsCount', $closedTicketsCount);
        $this->tpl->assign('ticketsInGoals', $ticketsInGoals);
        $this->tpl->assign('totalTodayCount', $totalToday);
        $this->tpl->assign('doneTodayCount', $doneToday);



        $allAssignedprojects = $this->projectsService->getProjectsAssignedToUser(session("userdata.id"), 'open');
        if(!is_array($allAssignedprojects)){
            $allAssignedprojects = [];
        }
        $this->tpl->assign("allProjects", $allAssignedprojects);
        $this->tpl->assign("projectCount", count($allAssignedprojects));
    }
}
