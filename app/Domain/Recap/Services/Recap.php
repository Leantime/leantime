<?php

namespace Leantime\Domain\Recap\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Recap\Repositories\Recap as RecapRepository;
use Leantime\Domain\Reminders\Services\RecipientAccess;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * Recaps of what a person got done: the one place that answers "what happened to my work over
 * this time", for the weekly summary's look-back first and, later, the apps' progress and
 * stand-up recaps. Rows are per to-do, so every client can list, count or group them.
 *
 * "Finished" (to-dos only, never milestones) means: in the range, the to-do was moved to a status
 * of the DONE type for its project, and either this person made that change or the to-do is
 * assigned to them (whoever made it). It must still be done at the end of the range: a to-do
 * that was re-opened later in the range doesn't count. A to-do that matches both ways is listed
 * once; finishedAt is the last qualifying change.
 *
 * Only to-dos in projects the person can still see, where their role holds tickets.view.
 */
class Recap
{
    /** The longest range one call reads. */
    public const MAX_RANGE_DAYS = 92;

    /** @var array<int, array<int, array<string, mixed>>> project id → status labels */
    private array $labels = [];

    /** @var array<string, bool> "userId|projectId" → may view (asked once per project per call) */
    private array $visible = [];

    public function __construct(
        private RecapRepository $recap,
        private TicketRepository $tickets,
        private ProjectRepository $projects,
        private RecipientAccess $access,
    ) {}

    /**
     * The to-dos you finished between two times (see the class description for what counts),
     * newest first.
     *
     * @param  string  $start  Start of the range, ISO 8601 (a time without an offset is UTC).
     * @param  string  $end  End of the range, ISO 8601. At most 92 days after the start.
     * @return array<int, array{taskId: int, headline: string, projectId: int, projectName: string, milestoneId: int|null, finishedAt: string, closedBy: array{userId: int, name: string}|null}>
     *
     * @api
     */
    public function finishedBetween(string $start, string $end): array
    {
        $userId = (int) session('userdata.id');
        if ($userId <= 0) {
            throw new AuthorizationException;
        }

        try {
            $startUtc = CarbonImmutable::parse($start, 'UTC')->setTimezone('UTC');
            $endUtc = CarbonImmutable::parse($end, 'UTC')->setTimezone('UTC');
        } catch (\Throwable) {
            throw new ValidationException(['start' => ['Use ISO 8601 dates, for example 2026-11-16T00:00:00Z.']]);
        }

        if ($endUtc->lessThanOrEqualTo($startUtc)) {
            throw new ValidationException(['end' => ['The end must be after the start.']]);
        }
        if ($startUtc->diffInDays($endUtc) > self::MAX_RANGE_DAYS) {
            throw new ValidationException(['end' => ['Ask for at most '.self::MAX_RANGE_DAYS.' days at a time.']]);
        }

        return $this->finishedBetweenFor($userId, $startUtc, $endUtc);
    }

    /**
     * The same as {@see finishedBetween()} for a person passed in, for callers without a session
     * (the weekly summary's scheduler). Not exposed over JSON-RPC: it takes any user id.
     *
     * @return array<int, array{taskId: int, headline: string, projectId: int, projectName: string, milestoneId: int|null, finishedAt: string, closedBy: array{userId: int, name: string}|null}>
     */
    public function finishedBetweenFor(int $userId, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $changes = $this->recap->statusChangesBetween(
            $userId,
            $startUtc->setTimezone('UTC')->format('Y-m-d H:i:s'),
            $endUtc->setTimezone('UTC')->format('Y-m-d H:i:s'),
        );

        /** @var array<int, array<int, array<string, mixed>>> $byTask */
        $byTask = [];
        foreach ($changes as $change) {
            $byTask[$change['ticketId']][] = $change;
        }

        $finished = [];
        foreach ($byTask as $taskId => $taskChanges) {
            $last = end($taskChanges);
            if (! $this->isDone($last['projectId'], $last['changeValue'])) {
                continue; // re-opened (or never done) by the end of the range
            }

            $assignedToThem = $last['editorId'] === (string) $userId;
            $qualifying = array_values(array_filter(
                $taskChanges,
                fn (array $change): bool => $this->isDone($change['projectId'], $change['changeValue'])
                    && ($assignedToThem || $change['changedBy'] === $userId)
            ));
            if ($qualifying === []) {
                continue;
            }

            if (! ($this->visible[$userId.'|'.$last['projectId']] ??= $this->canView($userId, $last['projectId']))) {
                continue;
            }

            $finishing = end($qualifying);
            $closedBy = $finishing['changedBy'] !== $userId && $finishing['changedBy'] > 0
                ? ['userId' => $finishing['changedBy'], 'name' => trim($finishing['changedByFirstname'].' '.$finishing['changedByLastname'])]
                : null;

            $finished[] = [
                'taskId' => $taskId,
                'headline' => trim(strip_tags($last['headline'])),
                'projectId' => $last['projectId'],
                'projectName' => trim(strip_tags($last['projectName'])),
                'milestoneId' => $last['milestoneId'],
                'finishedAt' => CarbonImmutable::parse($finishing['dateModified'], 'UTC')->toIso8601ZuluString(),
                'closedBy' => $closedBy,
            ];
        }

        usort($finished, static fn (array $a, array $b): int => [$b['finishedAt'], $b['taskId']] <=> [$a['finishedAt'], $a['taskId']]);

        return $finished;
    }

    /** Whether a status counts as done in its project (status type DONE, which includes archived). */
    protected function isDone(int $projectId, string $status): bool
    {
        if (! is_numeric($status)) {
            return false;
        }

        $this->labels[$projectId] ??= $this->tickets->getStateLabels($projectId);

        return ($this->labels[$projectId][(int) $status]['statusType'] ?? '') === 'DONE';
    }

    /** The person can still see the project and their role there holds tickets.view. */
    protected function canView(int $userId, int $projectId): bool
    {
        return $this->projects->isUserAssignedToProject($userId, $projectId)
            && $this->access->can($userId, $projectId, TicketsPermissions::VIEW);
    }
}
