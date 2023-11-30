<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;

class Welcome extends HtmxController
{
    /**
     * @var string
     */
    protected static $view = 'widgets::partials.welcome';

    private ProjectService $projectsService;
    private TicketService $ticketsService;
    private UserService $usersService;
    private TimesheetService $timesheetsService;
    private ReportService $reportsService;
    private SettingRepository $settingRepo;
    private CalendarRepository $calendarRepo;

    /**
     * Controller constructor
     *
     * @param \Leantime\Domain\Projects\Services\Projects $projectService The projects domain service.
     * @return void
     */
    public function init(
        ProjectService $projectsService,
        TicketService $ticketsService,
        UserService $usersService,
        TimesheetService $timesheetsService,
        ReportService $reportsService,
        SettingRepository $settingRepo,
        CalendarRepository $calendarRepo
    ) {
        $this->projectsService = $projectsService;
        $this->ticketsService = $ticketsService;
        $this->usersService = $usersService;
        $this->timesheetsService = $timesheetsService;
        $this->reportsService = $reportsService;
        $this->settingRepo = $settingRepo;
        $this->calendarRepo = $calendarRepo;

        $_SESSION['lastPage'] = BASE_URL . "/dashboard/home";
    }

    public function get()
    {

        $images = array(
            "undraw_smiley_face_re_9uid.svg",
            "undraw_meditation_re_gll0.svg",
            "undraw_fans_re_cri3.svg",
            "undraw_air_support_re_nybl.svg",
            "undraw_join_re_w1lh.svg",
            "undraw_blooming_re_2kc4.svg",
            "undraw_happy_music_g6wc.svg",
            "undraw_powerful_re_frhr.svg",
            "undraw_welcome_re_h3d9.svg",
            "undraw_joyride_re_968t.svg",
            "undraw_welcoming_re_x0qo.svg",
        );

        $randomKey = rand(0, count($images) - 1);

        $this->tpl->assign('randomImage', $images[$randomKey]);

        $currentUser = $this->usersService->getUser($_SESSION['userdata']['id']);
        $this->tpl->assign('currentUser', $currentUser);


        $tickets = $this->ticketsService->getOpenUserTicketsByProject($_SESSION["userdata"]["id"], '');
        $totalTickets = 0;
        foreach ($tickets as $ticketGroup) {
            $totalTickets = $totalTickets + count($ticketGroup["tickets"]);
        }

        $closedTicketsCount = 0;
        $closedTickets = $this->ticketsService->getRecentlyCompletedTicketsByUser($_SESSION["userdata"]["id"], null);
        if(is_array($closedTickets)){
            $closedTicketsCount = count($closedTickets);
        }

        $ticketsInGoals = 0;
        $goalTickets = $this->ticketsService->goalsRelatedToWork($_SESSION["userdata"]["id"], null);
        if(is_array($goalTickets)){
            $ticketsInGoals = count($goalTickets);
        }

        $todayTaskCount = 0;
        $todayStart = new \DateTime();
        $todayStart->setTimezone(new \DateTimeZone($_SESSION['usersettings.timezone']));
        $todayStart->setTime(0, 0, 0);

        $todayEnd = new \DateTime();
        $todayEnd->setTimezone(new \DateTimeZone($_SESSION['usersettings.timezone']));
        $todayEnd->setTime(23, 59, 59);

        $todaysTasks = $this->ticketsService->getScheduledTasks($todayStart, $todayEnd, $_SESSION["userdata"]["id"]);
        $totalToday = count($todaysTasks['totalTasks']) ?? 0;
        $doneToday = count($todaysTasks['doneTasks']) ?? 0;

        $this->tpl->assign('tickets', $tickets);
        $this->tpl->assign('totalTickets', $totalTickets);
        $this->tpl->assign('closedTicketsCount', $closedTicketsCount);
        $this->tpl->assign('ticketsInGoals', $ticketsInGoals);
        $this->tpl->assign('totalTodayCount', $totalToday);
        $this->tpl->assign('doneTodayCount', $doneToday);

        $allAssignedprojects = $this->projectsService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
        $this->tpl->assign("allProjects", $allAssignedprojects);
        $this->tpl->assign("projectCount", count($allAssignedprojects));
    }
}
