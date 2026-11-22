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
        $this->assignVariables($ticketId, (string) $this->incomingRequest->query->get('instance', ''), $this->reminders->getTaskReminders($ticketId));

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

        $preset = (string) $request->get('preset', '');
        $forAll = $request->get('forAllMyTodos') === '1';

        try {
            $state = $this->reminders->chooseTaskReminder($ticketId, $preset, $forAll);
            $this->tpl->assign('announce', sprintf(
                $this->language->__($forAll ? 'reminders.saved_for_all' : 'reminders.saved_choice'),
                $this->language->__('reminders.preset_'.$state['preset'])
            ));
        } catch (ValidationException) {
            $state = $this->reminders->getTaskReminders($ticketId);
            $this->tpl->assign('errorMessage', $this->language->__('reminders.picker_not_saved'));
            $this->tpl->assign('announce', $this->language->__('reminders.picker_not_saved'));
        }

        $this->assignVariables($ticketId, (string) $request->get('instance', ''), $state);

        return 'menu';
    }

    /**
     * @param  string  $instance  The menu's own id part, from the mount partial; anything else is
     *                            replaced, so it can't break the ids it goes into.
     * @param  array<string, mixed>  $state
     */
    private function assignVariables(int $ticketId, string $instance, array $state): void
    {
        $this->tpl->assign('ticketId', $ticketId);
        $this->tpl->assign('instance', preg_match('/^[a-f0-9]{1,16}$/', $instance) === 1 ? $instance : bin2hex(random_bytes(4)));
        $this->tpl->assign('reminder', $state);
    }
}
