<?php

namespace Leantime\Domain\Reminders\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Services\Reminders as ReminderService;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Remind me" on a to-do's ⋮ menu: a modal to pick how often this to-do reminds you, or to make
 * that your default for every to-do. Anyone who can see the to-do can set their own reminder; the
 * service checks that and stores the choice for the signed-in person only.
 */
class TaskReminder extends Controller
{
    private ReminderService $reminders;

    private TicketRepository $tickets;

    public function init(ReminderService $reminders, TicketRepository $tickets): void
    {
        $this->reminders = $reminders;
        $this->tickets = $tickets;
    }

    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function get($params): Response
    {
        $ticketId = (int) ($params['id'] ?? 0);
        $ticket = $this->tickets->getTicket($ticketId);
        if (! is_object($ticket)) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        // Checks the person can see the to-do (throws otherwise).
        $state = $this->reminders->getTaskReminders($ticketId);

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('reminder', $state);
        $this->tpl->assign('closeModal', isset($_GET['closeModal']));

        return $this->tpl->displayPartial('reminders.taskReminder');
    }

    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function post($params): Response
    {
        $ticketId = (int) ($_GET['id'] ?? 0);

        try {
            $this->reminders->chooseTaskReminder(
                $ticketId,
                (string) ($params['preset'] ?? ''),
                ! empty($params['forAllMyTodos'])
            );
            $this->tpl->setNotification($this->language->__('reminders.picker_saved'), 'success');
        } catch (ValidationException) {
            $this->tpl->setNotification($this->language->__('reminders.picker_not_saved'), 'error');

            return FrontcontrollerCore::redirect(BASE_URL.'/reminders/taskReminder/'.$ticketId);
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/reminders/taskReminder/'.$ticketId.'?closeModal=true');
    }
}
