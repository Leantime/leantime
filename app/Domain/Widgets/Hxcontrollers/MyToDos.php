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
use Leantime\Domain\Auth\Services\Auth as AuthService;

/**
 * Class MyToDos
 *
 * This class extends the HtmxController class and represents a controller for managing to-do items.
 */
class MyToDos extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'widgets::partials.myToDos';

    private TicketService $ticketsService;


    /**
     * Controller constructor
     *
     * @param \Leantime\Domain\Projects\Services\Projects $projectService The projects domain service.
     * @return void
     */
    public function init(
        TicketService $ticketsService,
    ) {
        $this->ticketsService = $ticketsService;
        $_SESSION['lastPage'] = BASE_URL . "/dashboard/home";
    }

    /**
     * Retrieves the todo widget assignments.
     *
     * @return void
     */
    public function get()
    {

        $params =  $this->incomingRequest->query->all();


        $tplVars = $this->ticketsService->getToDoWidgetAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }

    public function addTodo()
    {

        if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor, Roles::$commenter])) {
            if (isset($params['quickadd']) == true) {
                $result = $this->ticketsService->quickAddTicket($params);

                if (isset($result["status"])) {
                    $this->tpl->setNotification($result["message"], $result["status"]);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                }

                Frontcontroller::redirect(BASE_URL . "/dashboard/home");
            }
        }
    }
}
