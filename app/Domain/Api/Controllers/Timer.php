<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class Timer extends Controller
{
    private TimesheetService $timesheetService;

    /**
     * init - initialize private variables
     */
    public function init(TimesheetService $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * @param  array  $params  parameters or body of the request
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * @param  array  $params  parameters or body of the request
     */
    public function post(array $params): Response
    {
        if (isset($params['action']) === true && $params['action'] == 'start') {
            $ticketId = filter_var($params['ticketId'], FILTER_SANITIZE_NUMBER_INT);
            $this->timesheetService->punchIn($ticketId);

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if (isset($params['action']) === true && $params['action'] == 'stop') {
            $ticketId = filter_var($params['ticketId'], FILTER_SANITIZE_NUMBER_INT);
            $hoursBooked = $this->timesheetService->punchOut($ticketId);

            if (! $hoursBooked) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson($hoursBooked, 200);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 500);
    }
}
