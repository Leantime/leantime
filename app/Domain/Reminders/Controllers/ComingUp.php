<?php

namespace Leantime\Domain\Reminders\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Reminders\Services\ComingUpList;
use Leantime\Domain\Reminders\Services\Reminders as ReminderService;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Symfony\Component\HttpFoundation\Response;

/**
 * The "Coming up" panel (#/reminders/comingUp): everything the signed-in person's morning summary
 * covers, all of it rather than the top 5, in a dialog. The service builds it for the session
 * user only, with the summary's own permission rules. The summary's inbox row, email button and
 * push all open it.
 */
class ComingUp extends Controller
{
    private ReminderService $reminders;

    private ComingUpList $list;

    private ReminderSettings $settings;

    public function init(ReminderService $reminders, ComingUpList $list, ReminderSettings $settings): void
    {
        $this->reminders = $reminders;
        $this->list = $list;
        $this->settings = $settings;
    }

    public function get($params): Response
    {
        $settings = $this->reminders->getSettings();
        $panel = $this->list->present($this->reminders->getSummary(0), $settings['timezone']);

        $this->tpl->assign('panel', $panel);
        $this->tpl->assign('summaryOn', $settings['summary'] && $settings['workspaceEnabled']);
        $this->tpl->assign('summaryTime', ReminderSettings::formatTime($settings['summaryTime'], $this->settings->timeFormat((int) session('userdata.id'))));

        return $this->tpl->displayPartial('reminders.comingUp');
    }
}
