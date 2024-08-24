<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

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

    private Language $language;


    /**
     * Controller constructor
     *
     * @param \Leantime\Domain\Projects\Services\Projects $projectService The projects domain service.
     * @return void
     */
    public function init(
        TicketService $ticketsService,
        Language $language
    ) {
        $this->ticketsService = $ticketsService;
        $this->language = $language;
        session(["lastPage" => BASE_URL . "/dashboard/home"]);
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

        $params =  $_POST;

        if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            if (isset($params['quickadd']) == true) {
                $result = $this->ticketsService->quickAddTicket($params);

                if (isset($result["status"])) {
                    $this->tpl->setNotification($result["message"], $result["status"]);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                }

                $this->setHTMXEvent("HTMX.ShowNotification");

            }
        }

        $tplVars = $this->ticketsService->getToDoWidgetAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }
}
