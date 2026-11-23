<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;

/**
 * Suggestions for what to work on, from your own open to-dos: "Tasks to consider" and "Quick wins".
 *
 * The same rules the mobile app's Today screen uses, kept in one place so every client suggests
 * the same to-dos:
 *
 * - Tasks to consider: no due date, and either High or Critical priority or created in the last
 *   7 days. Recently added to-dos come first (newest first), then the rest by priority.
 * - Quick wins: sized XS or smaller (effort 0.5 or 1) and due within the next 7 days, including
 *   anything already past due. Earliest due first.
 *
 * Done to-dos, milestones and to-dos in closed projects are never suggested (the open-to-do list
 * already leaves them out). The two lists never overlap: one needs a due date, the other has none.
 * Days are read in the signed-in person's timezone.
 *
 * Personal: every method acts for the signed-in person and takes no user id.
 */
class Suggestions extends BaseService
{
    /** How many to-dos a list returns unless asked otherwise. */
    public const DEFAULT_LIMIT = 3;

    /** The most to-dos one call returns. */
    public const MAX_LIMIT = 100;

    /** A to-do created this many days ago or less counts as recently added. */
    public const RECENT_DAYS = 7;

    /** A quick win is due within this many days from today (or is already past due). */
    public const QUICK_WIN_DAYS = 7;

    /** Largest effort (story points) that still counts as a quick win: XS. */
    public const QUICK_WIN_MAX_EFFORT = 1.0;

    /** Priorities at or above this level (1 = Critical, 2 = High) count as important. */
    public const IMPORTANT_PRIORITY = 2;

    public function __construct(
        private Tickets $tickets,
    ) {}

    /**
     * Tasks to consider: undated to-dos that are important or recently added.
     *
     * @param  int  $limit  How many to return (1-100), or 0 for all of them.
     * @return array{tasks: list<array<string, mixed>>, total: int} The to-dos (the same fields as getAllOpenUserTickets, plus `reason`: "recent" or "high_priority"), and how many there are in total.
     *
     * @throws ValidationException When the limit is out of range.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function getTasksToConsider(int $limit = self::DEFAULT_LIMIT): array
    {
        $limit = self::validLimit($limit);
        $now = dtHelper()->userNow();
        $recentSince = $now->subDays(self::RECENT_DAYS);
        $timezone = $now->getTimezone()->getName();

        $candidates = [];
        foreach ($this->openTasks() as $task) {
            if (self::dueDay($task['dateToFinish'] ?? null, $timezone) !== null) {
                continue;
            }

            $created = self::createdAt($task['date'] ?? null);
            $isRecent = $created !== null && $created->greaterThanOrEqualTo($recentSince);
            $isImportant = self::priority($task['priority'] ?? null) <= self::IMPORTANT_PRIORITY;

            if (! $isRecent && ! $isImportant) {
                continue;
            }

            $task['reason'] = $isRecent ? 'recent' : 'high_priority';
            $candidates[] = ['task' => $task, 'recent' => $isRecent, 'created' => $created?->getTimestamp() ?? 0];
        }

        // Recently added first, newest first; then the rest by priority (Critical first), newest first.
        usort($candidates, function (array $a, array $b): int {
            if ($a['recent'] !== $b['recent']) {
                return $a['recent'] ? -1 : 1;
            }

            if (! $a['recent']) {
                $byPriority = self::priority($a['task']['priority'] ?? null) <=> self::priority($b['task']['priority'] ?? null);
                if ($byPriority !== 0) {
                    return $byPriority;
                }
            }

            return [$b['created'], (int) ($b['task']['id'] ?? 0)] <=> [$a['created'], (int) ($a['task']['id'] ?? 0)];
        });

        return self::page(array_column($candidates, 'task'), $limit);
    }

    /**
     * Quick wins: small to-dos (XS or smaller) due within the next 7 days, or already past due.
     *
     * @param  int  $limit  How many to return (1-100), or 0 for all of them.
     * @return array{tasks: list<array<string, mixed>>, total: int} The to-dos (the same fields as getAllOpenUserTickets, plus `dueDay`: YYYY-MM-DD in your timezone), earliest due first, and how many there are in total.
     *
     * @throws ValidationException When the limit is out of range.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function getQuickWins(int $limit = self::DEFAULT_LIMIT): array
    {
        $limit = self::validLimit($limit);
        $now = dtHelper()->userNow();
        $timezone = $now->getTimezone()->getName();
        $lastDay = $now->addDays(self::QUICK_WIN_DAYS)->format('Y-m-d');

        $quickWins = [];
        foreach ($this->openTasks() as $task) {
            $effort = is_numeric($task['storypoints'] ?? null) ? (float) $task['storypoints'] : 0.0;
            if ($effort <= 0 || $effort > self::QUICK_WIN_MAX_EFFORT) {
                continue;
            }

            $dueDay = self::dueDay($task['dateToFinish'] ?? null, $timezone);
            if ($dueDay === null || $dueDay > $lastDay) {
                continue;
            }

            $task['dueDay'] = $dueDay;
            $quickWins[] = $task;
        }

        usort($quickWins, fn (array $a, array $b): int => [$a['dueDay'], self::priority($a['priority'] ?? null), (int) ($a['id'] ?? 0)]
            <=> [$b['dueDay'], self::priority($b['priority'] ?? null), (int) ($b['id'] ?? 0)]);

        return self::page($quickWins, $limit);
    }

    /**
     * The signed-in person's open to-dos (no done to-dos, milestones or closed projects), limited
     * to projects where their role may view to-dos. getAllOpenUserTickets() checks project access,
     * not that permission, and the methods' entity-scoped gate leaves the check to this read.
     *
     * @return list<array<string, mixed>>
     */
    private function openTasks(): array
    {
        $userId = (int) session('userdata.id');
        if ($userId <= 0) {
            return [];
        }

        $canView = [];
        $visible = [];
        foreach ($this->tickets->getAllOpenUserTickets($userId) as $task) {
            $projectId = (int) ($task['projectId'] ?? 0);
            $canView[$projectId] ??= $projectId > 0 && $this->can(TicketsPermissions::VIEW, $projectId);
            if ($canView[$projectId]) {
                $visible[] = $task;
            }
        }

        return $visible;
    }

    /**
     * The day a to-do is due, as YYYY-MM-DD in the person's timezone, or null when it has none.
     *
     * Due dates are stored two ways: a date picked with a time (or the web's end of day), stored
     * in UTC and read in the person's timezone; and a plain calendar date from the mobile app,
     * stored as that date at 00:00 with no timezone, read as that date.
     */
    private static function dueDay(?string $dateToFinish, string $timezone): ?string
    {
        if ($dateToFinish === null || ! dtHelper()->isValidDateString($dateToFinish)) {
            return null;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]00:00:00/', $dateToFinish, $match) === 1) {
            return $match[1];
        }

        try {
            return dtHelper()->parseDbDateTime($dateToFinish)->setTimezone($timezone)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * When a to-do was created, or null when the stored value isn't a date.
     */
    private static function createdAt(?string $date): ?CarbonImmutable
    {
        if ($date === null || ! dtHelper()->isValidDateString($date)) {
            return null;
        }

        try {
            return dtHelper()->parseDbDateTime($date);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * A to-do's priority as a number (1 = Critical ... 5 = Lowest); no priority sorts last.
     */
    private static function priority(mixed $priority): int
    {
        return is_numeric($priority) && (int) $priority > 0 ? (int) $priority : 99;
    }

    /**
     * @throws ValidationException
     */
    private static function validLimit(int $limit): int
    {
        if ($limit < 0 || $limit > self::MAX_LIMIT) {
            throw new ValidationException(['limit' => ['Ask for between 1 and '.self::MAX_LIMIT.' to-dos, or 0 for all of them.']]);
        }

        return $limit;
    }

    /**
     * @param  list<array<string, mixed>>  $tasks
     * @return array{tasks: list<array<string, mixed>>, total: int}
     */
    private static function page(array $tasks, int $limit): array
    {
        return [
            'tasks' => $limit === 0 ? $tasks : array_slice($tasks, 0, $limit),
            'total' => count($tasks),
        ];
    }
}
