<?php

namespace Leantime\Domain\Reminders\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Services\Reminders as ReminderService;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Morning summary" and "Weekly summary" in the Notifications tab of your own settings: each on
 * or off with its time (in your timezone), the weekly day and look-back, and how often to-dos
 * remind you by default. Saves for the signed-in person only.
 * People who can see company settings also get the scheduler warning here when reminders
 * aren't going out.
 *
 * Saving only accepts POST: the Frontcontroller dispatches these actions on GET too, and a GET
 * skips the request-origin check.
 */
class SummarySettings extends HtmxController
{
    protected static string $view = 'reminders::partials.summarySettings';

    private ReminderService $reminders;

    public function init(ReminderService $reminders): void
    {
        $this->reminders = $reminders;
    }

    public function get(): void
    {
        $this->assignVariables();
    }

    public function save(): string|Response
    {
        if (! $this->incomingRequest->isMethod('POST')) {
            return $this->tpl->emptyResponse(405);
        }

        $request = $this->incomingRequest->request;

        try {
            // The weekly fields are left as they are when a form doesn't send them.
            $weeklyDay = $request->get('weeklyDay');

            $this->reminders->saveSettings(
                $request->get('summary') === '1',
                (string) $request->get('summaryTime', ''),
                (string) $request->get('taskPreset', ''),
                $request->has('weekly') ? $request->get('weekly') === '1' : null,
                $weeklyDay === null ? null : (ReminderSettings::isValidDay((string) $weeklyDay) ? (int) $weeklyDay : 0),
                $request->has('weeklyTime') ? (string) $request->get('weeklyTime') : null,
                $request->has('weeklyLookBack') ? $request->get('weeklyLookBack') === '1' : null,
            );
            $this->tpl->assign('savedMessage', $this->language->__('reminders.settings_saved'));
        } catch (ValidationException) {
            $this->tpl->assign('errorMessage', $this->language->__('reminders.settings_not_saved'));
        }

        $this->assignVariables();

        return 'form';
    }

    private function assignVariables(): void
    {
        $this->tpl->assign('settings', $this->reminders->getSettings());
        $this->tpl->assign('presets', ReminderSettings::PRESETS);
        $dayNames = explode(',', (string) $this->language->__('language.dayNames'));
        $this->tpl->assign('weekDays', array_combine(range(1, 7), array_map(fn (int $day): string => trim($dayNames[$day % 7] ?? (string) $day), range(1, 7))));
        $this->tpl->assign('showSchedulerWarning', $this->reminders->canManageWorkspace() && $this->reminders->schedulerLooksStopped());
    }
}
