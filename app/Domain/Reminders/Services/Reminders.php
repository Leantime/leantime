<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
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
     * @return array<string, mixed> See {@see SummaryBuilder::build()}.
     *
     * @api
     */
    public function getSummary(): array
    {
        $userId = $this->userId();
        $mine = $this->settings->forUser($userId);

        return $this->builder->build($userId, CarbonImmutable::now('UTC'), $mine['timezone'], $mine['taskPreset']);
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

        return $this->getSettings();
    }

    /**
     * How often one to-do reminds you.
     *
     * preset: what applies now. chosen: you picked it for this to-do; otherwise it follows
     * inherited, which is your default ("less" for to-dos that repeat every weekday, "off" when
     * your default is off). hasDueDate: reminders need a due date. summary / workspaceEnabled:
     * whether your morning summary, which carries these reminders, is on for you and for the
     * workspace.
     *
     * @return array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool}
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
     * already follows removes the choice, so it keeps following your default. With
     * $forAllMyTodos the pick becomes your default for every to-do, and this to-do follows it.
     *
     * @return array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool}
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
            $this->settings->save($userId, null, null, $preset);
            $this->settings->saveTaskChoice($userId, $ticketId, null);

            return $this->taskReminderState($userId, $ticket);
        }

        $inherited = $this->inheritedFor($userId, $ticketId);
        $this->settings->saveTaskChoice($userId, $ticketId, $preset === $inherited ? null : $preset);

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
        $this->visibleTicket($ticketId);

        if ($preset !== 'inherit' && ! in_array($preset, ReminderSettings::PRESETS, true)) {
            throw new ValidationException(['preset' => ['Use default, more, less, off or inherit.']]);
        }

        $this->settings->saveTaskChoice($userId, $ticketId, $preset === 'inherit' ? null : $preset);

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
     * @return array{preset: string, chosen: bool, inherited: string, defaultPreset: string, hasDueDate: bool, summary: bool, workspaceEnabled: bool}
     */
    private function taskReminderState(int $userId, object $ticket): array
    {
        $ticketId = (int) $ticket->id;
        $mine = $this->settings->forUser($userId);
        $chosen = $this->settings->taskChoice($userId, $ticketId);
        $inherited = $this->inheritedFor($userId, $ticketId);

        return [
            'preset' => $chosen ?? $inherited,
            'chosen' => $chosen !== null,
            'inherited' => $inherited,
            'defaultPreset' => $mine['taskPreset'],
            'hasDueDate' => self::isDate($ticket->dateToFinish ?? null),
            'summary' => $mine['summary'],
            'workspaceEnabled' => $this->settings->workspaceEnabled(),
        ];
    }

    private function inheritedFor(int $userId, int $ticketId): string
    {
        return ReminderSettings::inheritedPreset(
            $this->settings->forUser($userId)['taskPreset'],
            isset($this->reminders->frequentRecurring([$ticketId])[$ticketId])
        );
    }

    /** A real date (not empty and not MySQL's 0000-00-00 "no date"). */
    private static function isDate(mixed $value): bool
    {
        return is_string($value) && $value !== '' && ! str_starts_with($value, '0000') && strtotime($value) !== false;
    }

    private function userId(): int
    {
        $userId = (int) session('userdata.id');
        if ($userId <= 0) {
            throw new AuthorizationException;
        }

        return $userId;
    }

    /** The to-do, when it exists and the person can see it; otherwise not found / not allowed. */
    private function visibleTicket(int $ticketId): object
    {
        $ticket = $this->tickets->getTicket($ticketId);
        if (! is_object($ticket)) {
            throw new NotFoundException('That to-do was not found.');
        }

        $this->permissions->authorize(TicketsPermissions::VIEW, (int) $ticket->projectId);

        return $ticket;
    }
}
