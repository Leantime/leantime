<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
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
     * How often one to-do reminds you: the preset that applies, and whether you chose it for this
     * to-do (otherwise your default applies).
     *
     * @return array{preset: string, chosen: bool}
     *
     * @api
     */
    public function getTaskReminders(int $ticketId): array
    {
        $userId = $this->userId();
        $this->visibleTicket($ticketId);

        $chosen = $this->settings->taskChoice($userId, $ticketId);

        return [
            'preset' => $chosen ?? $this->settings->forUser($userId)['taskPreset'],
            'chosen' => $chosen !== null,
        ];
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
