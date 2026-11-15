<?php

namespace Leantime\Domain\Timesheets\Hxcontrollers;

use Error;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Events\Htmx\HtmxUiEvents;
use Leantime\Domain\Timesheets\Permissions\TimesheetsPermissions;
use Leantime\Domain\Timesheets\Services\Timesheets;

class Stopwatch extends HtmxController
{
    protected static string $view = 'timesheets::partials.stopwatch';

    private Timesheets $timesheetService;

    /**
     * Controller constructor
     */
    public function init(Timesheets $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * show stop watch
     */
    #[RequiresPermission(TimesheetsPermissions::VIEW, global: true)]
    public function getStatus(): void
    {

        $onTheClock = session()->exists('userdata') ? $this->timesheetService->isClocked(session('userdata.id')) : false;
        $this->tpl->assign('onTheClock', $onTheClock);
    }

    /**
     * show stop watch
     */
    #[RequiresPermission(TimesheetsPermissions::CREATE, global: true)]
    public function stopTimer(): void
    {
        if ($this->incomingRequest->getMethod() !== 'PATCH') {
            throw new Error('This endpoint only supports PATCH requests');
        }

        $params = $this->incomingRequest->request->all();
        $stopped = false;

        if (isset($params['action']) && $params['action'] === 'stop') {
            $ticketId = (int) filter_var($params['ticketId'], FILTER_SANITIZE_NUMBER_INT);
            $stopped = $this->timesheetService->punchOut($ticketId) !== false;
        }

        // A booked stop reports TimerStopped; anything else (no timer, a discarded one) still has
        // to bring the clicked control and the others back in line with the stored state.
        if (! $stopped) {
            $this->tpl->emit(HtmxUiEvents::TimerRefresh);
        }

        $onTheClock = session()->exists('userdata') ? $this->timesheetService->isClocked(session('userdata.id')) : false;
        $this->tpl->assign('onTheClock', $onTheClock);
    }

    #[RequiresPermission(TimesheetsPermissions::CREATE, global: true)]
    public function startTimer(): void
    {
        if ($this->incomingRequest->getMethod() !== 'PATCH') {
            throw new Error('This endpoint only supports PATCH requests');
        }

        $params = $this->incomingRequest->request->all();
        $started = false;

        if (isset($params['action']) && $params['action'] === 'start') {
            $ticketId = (int) filter_var($params['ticketId'], FILTER_SANITIZE_NUMBER_INT);

            if ($ticketId > 0) {
                $started = (bool) $this->timesheetService->punchIn($ticketId);

                if ($started) {
                    $this->tpl->setNotification(__('short_notifications.timer_started'), 'success');
                } else {
                    $this->tpl->setNotification(__('short_notifications.timer_start_failed'), 'error');
                }
            }
        }

        // A started timer reports TimerStarted; a failed start (e.g. a stale Start button while
        // another timer runs) still has to refresh the timer controls.
        if (! $started) {
            $this->tpl->emit(HtmxUiEvents::TimerRefresh);
        }

        $onTheClock = session()->exists('userdata') ? $this->timesheetService->isClocked(session('userdata.id')) : false;
        $this->tpl->assign('onTheClock', $onTheClock);
    }
}
