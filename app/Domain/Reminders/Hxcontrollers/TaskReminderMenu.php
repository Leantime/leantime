<?php

namespace Leantime\Domain\Reminders\Hxcontrollers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Services\Reminders as ReminderService;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Your reminders" inside the ⋮ menu of the open to-do: the same choices as the Remind me
 * picker, shown as menu items that save on tap. A second modal would replace the open to-do and
 * lose unsaved edits, so the choices live in the menu and swap themselves after a save.
 *
 * Saving only accepts POST: the Frontcontroller dispatches these actions on GET too, and a GET
 * skips the request-origin check. The service checks the person can see the to-do.
 */
class TaskReminderMenu extends HtmxController
{
    protected static string $view = 'reminders::partials.taskReminderMenu';

    private ReminderService $reminders;

    public function init(ReminderService $reminders): void
    {
        $this->reminders = $reminders;
    }

    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function get(): string
    {
        $ticketId = (int) $this->incomingRequest->query->get('ticketId', 0);
        $this->assignVariables($ticketId, $this->reminders->getTaskReminders($ticketId));

        return 'menu';
    }

    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function choose(): string|Response
    {
        if (! $this->incomingRequest->isMethod('POST')) {
            return $this->tpl->emptyResponse(405);
        }

        $request = $this->incomingRequest->request;
        $ticketId = (int) $request->get('ticketId', 0);

        try {
            $state = $this->reminders->chooseTaskReminder(
                $ticketId,
                (string) $request->get('preset', ''),
                $request->get('forAllMyTodos') === '1'
            );
        } catch (ValidationException) {
            $state = $this->reminders->getTaskReminders($ticketId);
            $this->tpl->assign('errorMessage', $this->language->__('reminders.picker_not_saved'));
        }

        $this->assignVariables($ticketId, $state);

        return 'menu';
    }

    /**
     * @param  array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool}  $state
     */
    private function assignVariables(int $ticketId, array $state): void
    {
        $this->tpl->assign('ticketId', $ticketId);
        $this->tpl->assign('reminder', $state);
    }
}
