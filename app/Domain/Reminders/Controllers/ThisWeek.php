<?php

namespace Leantime\Domain\Reminders\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Reminders\Services\ComingUpList;
use Leantime\Domain\Reminders\Services\Reminders as ReminderService;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Reminders\Services\WeeklyLines;
use Symfony\Component\HttpFoundation\Response;

/**
 * The "Your week" panel (#/reminders/thisWeek): the signed-in person's weekly summary, every item
 * rather than the top 5, in a dialog. The service builds it for the session user only, with the
 * summary's own permission rules. The weekly summary's inbox row, email button and push open it.
 */
class ThisWeek extends Controller
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

        $this->tpl->assign('panel', $this->list->presentWeek($this->reminders->getWeeklySummary(false, 0), $settings['timezone']));
        $this->tpl->assign('weeklyOn', $settings['weekly'] && $settings['workspaceEnabled']);
        $this->tpl->assign('weeklyDay', WeeklyLines::dayName($this->language, $settings['weeklyDay'], false));
        $this->tpl->assign('weeklyTime', ReminderSettings::formatTime($settings['weeklyTime'], $this->settings->timeFormat((int) session('userdata.id'))));

        return $this->tpl->displayPartial('reminders.thisWeek');
    }
}
