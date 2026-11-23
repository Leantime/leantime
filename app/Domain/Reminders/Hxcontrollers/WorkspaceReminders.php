<?php

namespace Leantime\Domain\Reminders\Hxcontrollers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Reminders\Services\Reminders as ReminderService;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Reminders" in company settings: one switch that can turn morning summaries off for the whole
 * workspace (on lets each person's own settings apply), and the warning when the scheduler
 * hasn't run in the last hour.
 *
 * Saving only accepts POST: the Frontcontroller dispatches these actions on GET too, and a GET
 * skips the request-origin check.
 */
class WorkspaceReminders extends HtmxController
{
    protected static string $view = 'reminders::partials.workspaceReminders';

    private ReminderService $reminders;

    public function init(ReminderService $reminders): void
    {
        $this->reminders = $reminders;
    }

    #[RequiresPermission(SettingPermissions::COMPANY_VIEW, global: true)]
    public function get(): void
    {
        $this->assignVariables();
    }

    #[RequiresPermission(SettingPermissions::COMPANY_EDIT, global: true)]
    public function save(): string|Response
    {
        if (! $this->incomingRequest->isMethod('POST')) {
            return $this->tpl->emptyResponse(405);
        }

        $this->reminders->setWorkspaceEnabled($this->incomingRequest->request->get('enabled') === '1');
        $this->tpl->assign('savedMessage', $this->language->__('reminders.settings_saved'));
        $this->assignVariables();

        return 'section';
    }

    private function assignVariables(): void
    {
        $this->tpl->assign('workspaceEnabled', $this->reminders->getSettings()['workspaceEnabled']);
        $this->tpl->assign('showSchedulerWarning', $this->reminders->schedulerLooksStopped());
    }
}
