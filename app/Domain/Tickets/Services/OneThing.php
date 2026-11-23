<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * "Your one thing": the one to-do a person picks as the thing that matters most on a given day.
 *
 * One shared store for every place that plans a day (the morning review, the weekly plan widget,
 * summaries), so they all read the same choice. It's personal: every method acts for the signed-in
 * person only and takes no user id.
 *
 * Stored in zp_settings under `usersettings.{userId}.oneThing.{YYYY-MM-DD}` as `{"taskId": N}`.
 * The date is the person's own local day; callers decide which day that is (this class never
 * converts time zones). The choice is always an existing to-do: a write-in is created by the
 * caller first (quick add) and then set here. Setting or clearing a one thing never changes the
 * to-do itself, including its Work Start and due dates.
 *
 * Permissions: the methods are entity-scoped, not tied to the session's current project (a
 * personal call must keep working whatever project happens to be selected). The precise check
 * is per to-do in visibleTask(): tickets.view in that to-do's own project, which includes project
 * access. Nothing is returned or stored for a to-do that check refuses.
 */
class OneThing extends BaseService
{
    /** Longest range getOneThings() reads in one call, in days (inclusive). */
    public const MAX_RANGE_DAYS = 62;

    public function __construct(
        private SettingRepository $settings,
        private TicketRepository $tickets,
    ) {}

    /**
     * Your one thing for a day.
     *
     * @param  string  $date  The day, as YYYY-MM-DD in your own time zone.
     * @return array{date: string, taskId: int, task: array{id: int, headline: string, projectId: int, status: int}}|null Null when nothing is set, or the to-do no longer exists or you can no longer see it.
     *
     * @throws ValidationException When the date isn't a real YYYY-MM-DD day.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function getOneThing(string $date): ?array
    {
        $date = $this->validDate($date, 'date');
        $userId = $this->requireUser();

        $taskId = self::taskIdFrom($this->settings->getSetting($this->key($userId, $date)));

        return $taskId === null ? null : $this->entry($date, $taskId);
    }

    /**
     * Makes a to-do your one thing for a day, replacing any earlier choice for that day.
     *
     * The to-do's dates are left exactly as they are.
     *
     * @param  string  $date  The day, as YYYY-MM-DD in your own time zone.
     * @param  int  $taskId  The to-do. It must exist and you must be able to see it.
     * @return array{date: string, taskId: int, task: array{id: int, headline: string, projectId: int, status: int}}
     *
     * @throws ValidationException When the date is invalid, or the to-do doesn't exist or you can't see it.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function setOneThing(string $date, int $taskId): array
    {
        $date = $this->validDate($date, 'date');
        $userId = $this->requireUser();

        // One message for "doesn't exist" and "can't see it", so the reply never reveals whether a hidden to-do exists.
        $entry = $taskId > 0 ? $this->entry($date, $taskId) : null;
        if ($entry === null) {
            throw new ValidationException(['taskId' => ["That to-do doesn't exist, or you can't see it."]]);
        }

        // saveSetting() reports false when the stored value didn't change, so its result isn't a failure signal.
        $this->settings->saveSetting($this->key($userId, $date), (string) json_encode(['taskId' => $taskId]));

        return $entry;
    }

    /**
     * Clears your one thing for a day. Clearing a day with nothing set is fine.
     *
     * @param  string  $date  The day, as YYYY-MM-DD in your own time zone.
     * @return bool True once the day has no one thing.
     *
     * @throws ValidationException When the date isn't a real YYYY-MM-DD day.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function clearOneThing(string $date): bool
    {
        $date = $this->validDate($date, 'date');
        $userId = $this->requireUser();

        $this->settings->deleteSetting($this->key($userId, $date));

        return true;
    }

    /**
     * Your one things for a range of days, oldest first. Days with nothing set, or whose to-do
     * no longer exists or is hidden from you, are left out.
     *
     * @param  string  $from  First day, YYYY-MM-DD.
     * @param  string  $until  Last day, YYYY-MM-DD, inclusive. At most 62 days after-and-including $from.
     * @return array<int, array{date: string, taskId: int, task: array{id: int, headline: string, projectId: int, status: int}}>
     *
     * @throws ValidationException When a date is invalid, $until is before $from, or the range is longer than 62 days.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function getOneThings(string $from, string $until): array
    {
        $from = $this->validDate($from, 'from');
        $until = $this->validDate($until, 'until');
        $userId = $this->requireUser();

        $start = new \DateTimeImmutable($from);
        $end = new \DateTimeImmutable($until);
        if ($end < $start) {
            throw new ValidationException(['until' => ['The last day must be on or after the first day.']]);
        }

        $days = (int) $start->diff($end)->days + 1;
        if ($days > self::MAX_RANGE_DAYS) {
            throw new ValidationException(['until' => ['Ask for '.self::MAX_RANGE_DAYS.' days or fewer at a time.']]);
        }

        $keys = [];
        for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
            $keys[$day->format('Y-m-d')] = $this->key($userId, $day->format('Y-m-d'));
        }

        // One read for the whole range.
        $stored = $this->settings->getSettingsForKeys(array_values($keys));

        $entries = [];
        $tasks = [];
        foreach ($keys as $date => $key) {
            $taskId = self::taskIdFrom($stored[$key] ?? false);
            if ($taskId === null) {
                continue;
            }

            // The same to-do on several days is looked up once.
            if (! array_key_exists($taskId, $tasks)) {
                $tasks[$taskId] = $this->visibleTask($taskId);
            }

            if ($tasks[$taskId] !== null) {
                $entries[] = ['date' => $date, 'taskId' => $taskId, 'task' => $tasks[$taskId]];
            }
        }

        return $entries;
    }

    /**
     * The task id stored in a setting value, or null when the value is missing or malformed.
     */
    public static function taskIdFrom(mixed $stored): ?int
    {
        if (! is_string($stored) || $stored === '') {
            return null;
        }

        $decoded = json_decode($stored, true);
        $taskId = is_array($decoded) ? ($decoded['taskId'] ?? null) : null;

        return is_int($taskId) && $taskId > 0 ? $taskId : null;
    }

    /**
     * @return array{date: string, taskId: int, task: array{id: int, headline: string, projectId: int, status: int}}|null
     */
    private function entry(string $date, int $taskId): ?array
    {
        $task = $this->visibleTask($taskId);

        return $task === null ? null : ['date' => $date, 'taskId' => $taskId, 'task' => $task];
    }

    /**
     * The to-do's public fields when it exists, isn't a milestone, and the signed-in person may view
     * to-dos in its project (role permission and project access); otherwise null.
     *
     * @return array{id: int, headline: string, projectId: int, status: int}|null
     */
    private function visibleTask(int $taskId): ?array
    {
        $ticket = $this->tickets->getTicket($taskId);
        if (! is_object($ticket) || (int) ($ticket->id ?? 0) !== $taskId) {
            return null;
        }

        if (($ticket->type ?? '') === 'milestone') {
            return null;
        }

        $projectId = (int) ($ticket->projectId ?? 0);
        if ($projectId <= 0 || ! $this->can(TicketsPermissions::VIEW, $projectId)) {
            return null;
        }

        return [
            'id' => $taskId,
            'headline' => (string) ($ticket->headline ?? ''),
            'projectId' => $projectId,
            'status' => (int) ($ticket->status ?? 0),
        ];
    }

    private function key(int $userId, string $date): string
    {
        return 'usersettings.'.$userId.'.oneThing.'.$date;
    }

    /**
     * A strict, real calendar day (YYYY-MM-DD), returned unchanged.
     *
     * @throws ValidationException
     */
    private function validDate(string $date, string $field): string
    {
        // Shape first: createFromFormat() throws a ValueError on input with a NUL byte.
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) !== 1) {
            throw new ValidationException([$field => ['Use a date like 2026-11-23.']]);
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            throw new ValidationException([$field => ['Use a date like 2026-11-23.']]);
        }

        return $date;
    }

    /**
     * The signed-in person's id.
     *
     * @throws AuthorizationException When nobody is signed in.
     */
    private function requireUser(): int
    {
        $userId = $this->currentUserId();
        if ($userId === null || $userId <= 0) {
            throw new AuthorizationException('Sign in to use your one thing.');
        }

        return $userId;
    }
}
