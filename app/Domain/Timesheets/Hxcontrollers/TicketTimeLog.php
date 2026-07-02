<?php

namespace Leantime\Domain\Timesheets\Hxcontrollers;

use Leantime\Core\Controller\HxComponent;
use Leantime\Core\Events\Htmx\HtmxEvent;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Htmx\HtmxTimesheetEvents;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;

/**
 * Ticket time-tracking totals + manager-only time entry list/edit component.
 *
 * Mounted with <x-global::hx :for="self::class" :id="$ticketId" />. Totals are visible to
 * anyone who can view the ticket (mirrors the previously-static totals block); the individual
 * entry list and its inline "edit duration" action are additionally restricted to Manager+
 * (see Timesheets::getTimeEntriesForTicket()/updateTimeDuration()) and are re-checked here
 * before the entries are even fetched, so a non-manager never triggers that query.
 */
class TicketTimeLog extends HxComponent
{
    protected static string $view = 'timesheets::partials.ticketTimeLog';

    /** Refresh swaps the inner content so the mount wrapper keeps its id + event triggers. */
    public static string $swap = 'innerHTML';

    private TimesheetService $timesheetService;

    private TicketService $ticketService;

    public function init(TimesheetService $timesheetService, TicketService $ticketService): void
    {
        $this->timesheetService = $timesheetService;
        $this->ticketService = $ticketService;
    }

    public static function route(): string
    {
        return 'timesheets/ticketTimeLog';
    }

    /**
     * @return array<int, HtmxEvent>
     */
    public static function listensTo(): array
    {
        return [HtmxTimesheetEvents::ENTRY_UPDATED];
    }

    /**
     * @return array<int, HtmxEvent>
     */
    public static function emits(): array
    {
        return [HtmxTimesheetEvents::ENTRY_UPDATED];
    }

    public function get(): void
    {
        $getVars = $_GET;
        $ticketId = (int) ($getVars['ticketId'] ?? $getVars['id'] ?? 0);

        $this->renderTimeLog($ticketId);
    }

    public function save(): void
    {
        $postVars = $_POST;
        $ticketId = (int) ($postVars['ticketId'] ?? $_GET['ticketId'] ?? $_GET['id'] ?? 0);
        $id = (int) ($postVars['id'] ?? 0);
        // Whole hours only: ctype_digit rejects decimals ("3.5"), signs ("-5"), and non-numeric
        // input in one check, so "6" is the only shape that passes through to the service.
        $hours = trim((string) ($postVars['hours'] ?? ''));

        if ($hours === '' || ! ctype_digit($hours) || (int) $hours <= 0) {
            $this->tpl->setNotification($this->language->__('notifications.timesheet_hours_invalid'), 'error');
        } elseif ($this->timesheetService->updateTimeDuration($id, (int) $hours)) {
            $this->tpl->setNotification($this->language->__('notifications.timesheet_hours_updated'), 'success');
            $this->tpl->emit(HtmxTimesheetEvents::ENTRY_UPDATED, HtmxTimesheetEvents::ENTRY_UPDATED->scoped($ticketId));
        } else {
            $this->tpl->setNotification($this->language->__('notifications.timesheet_hours_invalid'), 'error');
        }

        $this->renderTimeLog($ticketId);
    }

    private function renderTimeLog(int $ticketId): void
    {
        $ticket = $this->ticketService->getTicket($ticketId);
        $isManager = AuthService::userIsAtLeast(Roles::$manager, true);

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('timesheetsAllHours', $this->timesheetService->getSumLoggedHoursForTicket($ticketId));
        $this->tpl->assign('remainingHours', $ticket ? $this->timesheetService->getRemainingHours($ticket) : 0);
        $this->tpl->assign('isManager', $isManager);
        $this->tpl->assign('timeEntries', $isManager ? $this->timesheetService->getTimeEntriesForTicket($ticketId) : []);
    }
}
