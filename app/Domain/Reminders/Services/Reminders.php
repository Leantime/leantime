<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Events\ReminderDefaultsUpdated;
use Leantime\Domain\Reminders\Events\TaskReminderUpdated;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * Reminders for the person who is signed in: their morning summary as data, their reminder
 * settings, and the reminders on one to-do. Every method acts for the session user only; none
 * takes another person's id. Methods about a to-do check that the person can see it.
 */
class Reminders
{
    public function __construct(
        private ReminderSettings $settings,
        private SummaryBuilder $builder,
        private TicketRepository $tickets,
        private PermissionService $permissions,
        private ReminderRepository $reminders,
    ) {}

    /**
     * What is coming up for you today: the same list the morning summary sends.
     *
     * @param  int  $limit  How many items to list: 5 by default (as the morning summary does, the
     *                      rest counted in moreCount); 0 for the whole list. The routine line,
     *                      counts and item fields are the same either way.
     * @return array<string, mixed> See {@see SummaryBuilder::build()}.
     *
     * @api
     */
    public function getSummary(int $limit = SummaryBuilder::MAX_ITEMS): array
    {
        $userId = $this->userId();
        $mine = $this->settings->forUser($userId);

        return $this->builder->build($userId, CarbonImmutable::now('UTC'), $mine['timezone'], $mine['taskPreset'], max(0, $limit));
    }

    /**
     * Your reminder settings, plus whether the workspace has reminders turned off.
     *
     * @return array{summary: bool, summaryTime: string, taskPreset: string, timezone: string, workspaceEnabled: bool}
     *
     * @api
     */
    public function getSettings(): array
    {
        return $this->settings->forUser($this->userId()) + ['workspaceEnabled' => $this->settings->workspaceEnabled()];
    }

    /**
     * Changes your reminder settings. Leave a value out to keep it.
     *
     * @param  bool|null  $summary  Morning summary on or off.
     * @param  string|null  $summaryTime  'HH:MM' in your timezone.
     * @param  string|null  $taskPreset  Your default for to-dos: default, more, less or off.
     * @return array{summary: bool, summaryTime: string, taskPreset: string, timezone: string, workspaceEnabled: bool}
     *
     * @api
     */
    public function saveSettings(?bool $summary = null, ?string $summaryTime = null, ?string $taskPreset = null): array
    {
        $userId = $this->userId();

        if ($summaryTime !== null && ! ReminderSettings::isValidTime($summaryTime)) {
            throw new ValidationException(['summaryTime' => ['Use a time like 08:00.']]);
        }
        if ($taskPreset !== null && ! in_array($taskPreset, ReminderSettings::PRESETS, true)) {
            throw new ValidationException(['taskPreset' => ['Use default, more, less or off.']]);
        }

        $this->settings->save($userId, $summary, $summaryTime, $taskPreset);
        ReminderDefaultsUpdated::dispatch(userId: $userId);

        return $this->getSettings();
    }

    /**
     * How often one to-do reminds you.
     *
     * isYours: assigned to you, or unassigned and created by you. Reminders on anyone else's
     * to-do you can see are an opt-in for yourself: off until you pick one, and they never
     * remind its person. preset: what applies now. chosen: you picked it for this to-do;
     * otherwise it follows inherited, which is "off" when it isn't yours, else your default
     * ("less" for to-dos that repeat every weekday, "off" when your default is off). hasDueDate: reminders need a due date. summary / workspaceEnabled:
     * whether your morning summary, which carries these reminders, is on for you and for the
     * workspace.
     *
     * @return array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool, isYours: bool}
     *
     * @api
     */
    public function getTaskReminders(int $ticketId): array
    {
        $userId = $this->userId();
        $ticket = $this->visibleTicket($ticketId);

        return $this->taskReminderState($userId, $ticket);
    }

    /**
     * Picks how often one to-do reminds you (default, more, less or off). Picking what the to-do
     * already follows removes the choice (on someone else's to-do, picking off stops the
     * reminders). With $forAllMyTodos the pick becomes your default for every to-do of yours, and
     * this to-do follows it (or keeps the pick when it would inherit something else, as a routine
     * does); it is refused on a to-do that isn't yours.
     *
     * @return array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool, isYours: bool}
     *
     * @api
     */
    public function chooseTaskReminder(int $ticketId, string $preset, bool $forAllMyTodos = false): array
    {
        $userId = $this->userId();
        $ticket = $this->visibleTicket($ticketId);

        if (! in_array($preset, ReminderSettings::PRESETS, true)) {
            throw new ValidationException(['preset' => ['Use default, more, less or off.']]);
        }

        if ($forAllMyTodos) {
            if (! $this->isYours($userId, $ticket)) {
                throw new ValidationException(['forAllMyTodos' => ['"Use this for all my to-dos" is only for your own to-dos.']]);
            }
            $this->settings->save($userId, null, null, $preset);
            // This to-do keeps the pick even when it would inherit something else under the new
            // default (a routine every weekday inherits "only the morning it's due").
            $inherited = $this->inheritedFor($userId, $ticket);
            $this->settings->saveTaskChoice($userId, $ticketId, $preset === $inherited ? null : $preset);
            ReminderDefaultsUpdated::dispatch(userId: $userId);

            return $this->taskReminderState($userId, $ticket);
        }

        $inherited = $this->inheritedFor($userId, $ticket);
        $this->settings->saveTaskChoice($userId, $ticketId, $preset === $inherited ? null : $preset);
        TaskReminderUpdated::dispatch(ticketId: $ticketId, userId: $userId);

        return $this->taskReminderState($userId, $ticket);
    }

    /**
     * Chooses how often one to-do reminds you: default, more, less or off. "inherit" removes the
     * choice so your own default applies again.
     *
     * @api
     */
    public function setTaskPreset(int $ticketId, string $preset): bool
    {
        $userId = $this->userId();
        $ticket = $this->visibleTicket($ticketId);

        if ($preset !== 'inherit' && ! in_array($preset, ReminderSettings::PRESETS, true)) {
            throw new ValidationException(['preset' => ['Use default, more, less, off or inherit.']]);
        }

        // "off" on someone else's to-do removes the opt-in (as the menu does) rather than storing
        // it, so it can't silence the person's own default if the to-do becomes theirs later. On
        // their own to-dos an explicit "off" is kept.
        $remove = $preset === 'inherit' || ($preset === ReminderSettings::PRESET_OFF && ! $this->isYours($userId, $ticket));
        $this->settings->saveTaskChoice($userId, $ticketId, $remove ? null : $preset);
        TaskReminderUpdated::dispatch(ticketId: $ticketId, userId: $userId);

        return true;
    }

    /**
     * Turns reminders on or off for the whole workspace. Off stops every morning summary; on lets
     * each person's own settings apply again. Needs the right to edit company settings.
     */
    public function setWorkspaceEnabled(bool $enabled): bool
    {
        $this->permissions->authorize(SettingPermissions::COMPANY_EDIT, null, true);
        $this->settings->saveWorkspaceEnabled($enabled);

        return true;
    }

    /**
     * True when the reminder job hasn't run in the last hour, so summaries aren't going out. Only
     * shown to people who can see company settings.
     */
    public function schedulerLooksStopped(?CarbonImmutable $nowUtc = null): bool
    {
        return ! $this->settings->schedulerRanSince($nowUtc ?? CarbonImmutable::now('UTC'));
    }

    /** Whether the signed-in person may see the workspace switch and the scheduler warning. */
    public function canManageWorkspace(): bool
    {
        return $this->permissions->currentUserCan(SettingPermissions::COMPANY_VIEW, null, true);
    }

    /**
     * @return array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool, isYours: bool}
     */
    private function taskReminderState(int $userId, object $ticket): array
    {
        $ticketId = (int) $ticket->id;
        $mine = $this->settings->forUser($userId);
        $chosen = $this->settings->taskChoice($userId, $ticketId);
        $inherited = $this->inheritedFor($userId, $ticket);

        return [
            // The same resolver the summary uses, so this is the preset reminders will follow.
            'preset' => ReminderSettings::effectivePreset($chosen, $mine['taskPreset'], $this->isFrequentRoutine($ticketId), $this->isYours($userId, $ticket)),
            'chosen' => $chosen !== null,
            'inherited' => $inherited,
            'defaultPreset' => $mine['taskPreset'],
            'hasDueDate' => self::isDate($ticket->dateToFinish ?? null),
            'summary' => $mine['summary'],
            'workspaceEnabled' => $this->settings->workspaceEnabled(),
            'isYours' => $this->isYours($userId, $ticket),
        ];
    }

    /**
     * What the to-do follows without a choice: off when it isn't yours (an opt-in), otherwise
     * your default ("less" for routines every weekday).
     */
    private function inheritedFor(int $userId, object $ticket): string
    {
        return ReminderSettings::effectivePreset(
            null,
            $this->settings->forUser($userId)['taskPreset'],
            $this->isFrequentRoutine((int) $ticket->id),
            $this->isYours($userId, $ticket)
        );
    }

    private function isFrequentRoutine(int $ticketId): bool
    {
        return isset($this->reminders->frequentRecurring([$ticketId])[$ticketId]);
    }

    /** Assigned to you, or unassigned and created by you. */
    private function isYours(int $userId, object $ticket): bool
    {
        return ReminderRepository::isOwner($userId, $ticket->editorId ?? null, $ticket->userId ?? null);
    }

    /** A real date: not empty and not one of Leantime's "no date" sentinels (as the summary reads it). */
    private static function isDate(mixed $value): bool
    {
        return ReminderRepository::isRealDate($value);
    }

    private function userId(): int
    {
        $userId = (int) session('userdata.id');
        if ($userId <= 0) {
            throw new AuthorizationException;
        }

        return $userId;
    }

    /**
     * The to-do, when it exists and the person can see it; otherwise not found / not allowed.
     * Milestones are refused (after the access check): they show in the summary on their own fixed
     * notice days and ignore reminder choices, so a saved choice would have no effect.
     */
    private function visibleTicket(int $ticketId): object
    {
        $ticket = $this->tickets->getTicket($ticketId);
        if (! is_object($ticket)) {
            throw new NotFoundException('That to-do was not found.');
        }

        $this->permissions->authorize(TicketsPermissions::VIEW, (int) $ticket->projectId);

        if (($ticket->type ?? '') === 'milestone') {
            throw new ValidationException(['ticketId' => ['Milestones have no reminder choices; they appear in your summary ahead of their date on their own.']]);
        }

        return $ticket;
    }
}
