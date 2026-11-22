<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * What is coming up for one person on one local day. The one builder every channel uses: the
 * morning email, push and inbox row, and the API that the web and other clients render.
 *
 * What counts as theirs:
 *  - To-dos assigned to them, or unassigned and created by them. When each one shows depends on
 *    the task's preset (their choice for that task, else their default):
 *      default  the day before it's due and the morning it's due
 *      more     every morning from its start date (or 3 days before, without one) until it's due
 *      less     the morning it's due
 *      off      never
 *    To-dos that repeat daily or every weekday use "less" unless the person chose otherwise for
 *    that task, and are grouped into one "routine" line instead of listed.
 *  - Anyone else's to-do they can see and chose a reminder for ("Remind me"). Those are off until
 *    they pick one: an opt-in for themselves that never reminds the to-do's person. Ownership is
 *    read each day, so a stored choice keeps working when they become, or stop being, the
 *    assignee; with no stored choice the to-do follows the rules for whose it is that day.
 *  - Milestones they own (assigned, or unassigned and created by them): a week before and the
 *    day before they end.
 *  - Projects they're on the team of that end: a week before and the day before. Only the
 *    team's managers get these; when nobody on the team is a manager, everyone on it does.
 *
 * Done to-dos and milestones, closed and archived projects, projects the person can no longer
 * see, and anything their own role there may not view (tickets.view for to-dos and milestones,
 * projects.view for project ends) are left out. Overdue to-dos are one count, never a list, and alone they never make
 * a summary: nothing coming up means nothing to send.
 *
 * Every day is the person's local calendar day in their timezone.
 */
class SummaryBuilder
{
    /** Listed items; the rest become "and N more". */
    public const MAX_ITEMS = 5;

    /** Days before the due date "more" starts when a to-do has no start date. */
    public const MORE_DAYS_WITHOUT_START = 3;

    /** Days before a milestone or project ends that it shows. */
    public const END_NOTICE_DAYS = [7, 1];

    /** @var array<int, array<int, array<string, mixed>>> project id → status labels */
    private array $labels = [];

    /** @var array<string, bool> "userId|projectId" → can see */
    private array $access = [];

    /** @var array<int, array<int, int>> project id → user ids who get its end notice */
    private array $endRecipients = [];

    private ?RecipientAccess $recipientAccess = null;

    public function __construct(
        private ReminderRepository $reminders,
        private TicketRepository $tickets,
        private ProjectRepository $projects,
        private TrustedAppUrl $trustedAppUrl,
    ) {}

    /**
     * @param  CarbonImmutable  $nowUtc  The current time.
     * @param  string  $timezone  The person's timezone.
     * @param  string  $defaultPreset  Their default preset for to-dos.
     * @param  int  $limit  How many items to list ({@see MAX_ITEMS} for the email and push); 0 lists
     *                      them all, for the full "Coming up" list. The rest is moreCount.
     * @return array{
     *     date: string,
     *     items: array<int, array{kind: string, id: int, title: string, projectId: int, projectName: string, due: string, daysUntil: int, url: string}>,
     *     moreCount: int,
     *     routine: array{count: int, items: array<int, array{kind: string, id: int, title: string, projectId: int, projectName: string, due: string, daysUntil: int, url: string}>},
     *     overdueCount: int,
     *     dueToday: int,
     *     dueTomorrow: int,
     *     later: int,
     *     isEmpty: bool
     * }
     */
    public function build(int $userId, CarbonImmutable $nowUtc, string $timezone, string $defaultPreset = ReminderSettings::PRESET_DEFAULT, int $limit = self::MAX_ITEMS): array
    {
        $today = $nowUtc->setTimezone($timezone)->startOfDay();
        $todayIndex = self::dayIndex($today->toDateString());
        $overdueCount = 0;

        $entries = array_merge(
            $this->taskEntries($userId, $today, $todayIndex, $timezone, $defaultPreset, $overdueCount),
            $this->milestoneEntries($userId, $today, $todayIndex, $timezone),
            $this->projectEntries($userId, $today, $todayIndex, $timezone),
        );

        $listed = array_values(array_filter($entries, fn (array $entry) => ! $entry['routine']));
        $routine = array_values(array_filter($entries, fn (array $entry) => $entry['routine']));

        usort($listed, static fn (array $a, array $b) => [$a['item']['daysUntil'], $a['rank'], $a['priority'], $a['item']['title'], $a['item']['id']]
            <=> [$b['item']['daysUntil'], $b['rank'], $b['priority'], $b['item']['title'], $b['item']['id']]);

        $items = array_map(fn (array $entry) => $entry['item'], $listed);
        $routineItems = array_map(fn (array $entry) => $entry['item'], $routine);
        $all = array_merge($items, $routineItems);

        $count = static fn (callable $test) => count(array_filter($all, $test));

        return [
            'date' => $today->toDateString(),
            'items' => $limit > 0 ? array_slice($items, 0, $limit) : $items,
            'moreCount' => $limit > 0 ? max(0, count($items) - $limit) : 0,
            'routine' => ['count' => count($routineItems), 'items' => $routineItems],
            'overdueCount' => $overdueCount,
            'dueToday' => $count(fn (array $item) => $item['daysUntil'] === 0),
            'dueTomorrow' => $count(fn (array $item) => $item['daysUntil'] === 1),
            'later' => $count(fn (array $item) => $item['daysUntil'] > 1),
            'isEmpty' => $all === [],
        ];
    }

    /**
     * Which presets show a to-do today, given how many days until it's due.
     *
     * @param  int|null  $daysSinceStart  Days since its start date (0 = starts today), null without one.
     */
    public static function presetShows(string $preset, int $daysUntil, ?int $daysSinceStart): bool
    {
        if ($daysUntil < 0) {
            return false;
        }

        return match ($preset) {
            ReminderSettings::PRESET_DEFAULT => $daysUntil <= 1,
            ReminderSettings::PRESET_LESS => $daysUntil === 0,
            ReminderSettings::PRESET_MORE => $daysUntil <= 1
                || ($daysSinceStart !== null ? $daysSinceStart >= 0 : $daysUntil <= self::MORE_DAYS_WITHOUT_START),
            default => false,
        };
    }

    /** Days since 1970-01-01 for a `Y-m-d` date: day arithmetic without DST surprises. */
    public static function dayIndex(string $date): int
    {
        return intdiv((new \DateTimeImmutable($date.' 00:00:00', new \DateTimeZone('UTC')))->getTimestamp(), 86400);
    }

    /**
     * @return array<int, array{item: array<string, mixed>, rank: int, priority: int, routine: bool}>
     */
    private function taskEntries(int $userId, CarbonImmutable $today, int $todayIndex, string $timezone, string $defaultPreset, int &$overdueCount): array
    {
        $overdueCount = 0;

        // Choices on to-dos that aren't theirs are opt-ins: those to-dos join the summary too.
        $chosen = array_filter($this->reminders->taskChoices($userId), fn (string $preset) => in_array($preset, ReminderSettings::PRESETS, true));
        $optedIn = array_keys(array_filter($chosen, fn (string $preset) => $preset !== ReminderSettings::PRESET_OFF));

        $rows = $this->reminders->tasksFor(
            $userId,
            self::utc($today),
            self::utc($today->endOfDay()),
            self::utc($today->addDays(self::MORE_DAYS_WITHOUT_START)->endOfDay()),
            $optedIn,
        );
        $rows = array_values(array_filter($rows, fn (array $row) => ! $this->isDone((int) $row['projectId'], $row['status'])
            && $this->canSee($userId, (int) $row['projectId'])
            && $this->mayView($userId, (int) $row['projectId'], TicketsPermissions::VIEW)));

        $ids = array_map(fn (array $row) => (int) $row['id'], $rows);
        $frequent = $this->reminders->frequentRecurring($ids);

        $entries = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $isRoutine = isset($frequent[$id]);
            $isYours = (bool) ($row['isYours'] ?? true);
            $preset = ReminderSettings::effectivePreset($chosen[$id] ?? null, $defaultPreset, $isRoutine, $isYours);
            if ($preset === ReminderSettings::PRESET_OFF) {
                continue;
            }

            $daysUntil = $this->localDayIndex((string) $row['dateToFinish'], $timezone) - $todayIndex;
            if ($daysUntil < 0) {
                $overdueCount++;

                continue;
            }

            $start = self::isDate($row['editFrom'] ?? null) ? $this->localDayIndex((string) $row['editFrom'], $timezone) : null;
            if (! self::presetShows($preset, $daysUntil, $start === null ? null : $todayIndex - $start)) {
                continue;
            }

            $entries[] = [
                'item' => $this->item('task', $id, (string) $row['headline'], (int) $row['projectId'], (string) ($row['projectName'] ?? ''), (string) $row['dateToFinish'], $daysUntil, '/dashboard/home#/tickets/showTicket/'.$id, $isYours),
                'rank' => 2,
                // 0 is "no priority", sorted with the missing ones (as the to-do lists sort it).
                'priority' => is_numeric($row['priority'] ?? null) && (int) $row['priority'] > 0 ? (int) $row['priority'] : 99,
                // Only today's routines become the routine line; a later occurrence ("more") is
                // listed under its own day.
                'routine' => $isRoutine && $daysUntil === 0,
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array{item: array<string, mixed>, rank: int, priority: int, routine: bool}>
     */
    private function milestoneEntries(int $userId, CarbonImmutable $today, int $todayIndex, string $timezone): array
    {
        $rows = $this->reminders->milestonesFor($userId, self::utc($today->addDay()), self::utc($today->addDays(max(self::END_NOTICE_DAYS))->endOfDay()));

        $entries = [];
        foreach ($rows as $row) {
            if ($this->isDone((int) $row['projectId'], $row['status']) || ! $this->canSee($userId, (int) $row['projectId'])
                || ! $this->mayView($userId, (int) $row['projectId'], TicketsPermissions::VIEW)) {
                continue;
            }

            $daysUntil = $this->localDayIndex((string) $row['editTo'], $timezone) - $todayIndex;
            if (! in_array($daysUntil, self::END_NOTICE_DAYS, true)) {
                continue;
            }

            $id = (int) $row['id'];
            $entries[] = [
                'item' => $this->item('milestone', $id, (string) $row['headline'], (int) $row['projectId'], (string) ($row['projectName'] ?? ''), (string) $row['editTo'], $daysUntil, '/dashboard/home#/tickets/editMilestone/'.$id),
                'rank' => 1,
                'priority' => 0,
                'routine' => false,
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array{item: array<string, mixed>, rank: int, priority: int, routine: bool}>
     */
    private function projectEntries(int $userId, CarbonImmutable $today, int $todayIndex, string $timezone): array
    {
        $rows = $this->reminders->projectsEndingFor($userId, self::utc($today->addDay()), self::utc($today->addDays(max(self::END_NOTICE_DAYS))->endOfDay()));

        $entries = [];
        foreach ($rows as $row) {
            $daysUntil = $this->localDayIndex($row['end'], $timezone) - $todayIndex;
            if (! in_array($daysUntil, self::END_NOTICE_DAYS, true) || ! in_array($userId, $this->endRecipients($row['id']), true)
                || ! $this->mayView($userId, $row['id'], ProjectsPermissions::VIEW)) {
                continue;
            }

            $entries[] = [
                'item' => $this->item('project', $row['id'], $row['name'], $row['id'], $row['name'], $row['end'], $daysUntil, '/projects/changeCurrentProject/'.$row['id']),
                'rank' => 0,
                'priority' => 0,
                'routine' => false,
            ];
        }

        return $entries;
    }

    /**
     * Who gets a project's end notice: its managers, or everyone on the team when it has none.
     *
     * @return array<int, int>
     */
    private function endRecipients(int $projectId): array
    {
        if (isset($this->endRecipients[$projectId])) {
            return $this->endRecipients[$projectId];
        }

        $team = $this->reminders->projectTeam($projectId);
        $managers = array_values(array_filter($team, fn (array $member) => $this->isManager($member['role'], $member['projectRole'])));

        return $this->endRecipients[$projectId] = array_map(fn (array $member) => $member['userId'], $managers !== [] ? $managers : $team);
    }

    /**
     * Whether a team member counts as a manager of the project: a company role of manager or
     * above, or a project role of manager. Same resolution as the permission system's effective
     * project role: manager and above keep their company role; below that an assignable project
     * role wins.
     */
    protected function isManager(string $role, string $projectRole): bool
    {
        $companyRole = Roles::getRoleString(ctype_digit($role) ? (int) $role : $role) ?: $role;
        if (Roles::isAtLeast($companyRole, Roles::$manager)) {
            return true;
        }

        if ($projectRole === '') {
            return false;
        }

        $assignable = Roles::getProjectAssignableRoles();
        $resolved = ctype_digit($projectRole) ? ($assignable[(int) $projectRole] ?? null) : (in_array($projectRole, $assignable, true) ? $projectRole : null);

        return $resolved !== null && Roles::isAtLeast($resolved, Roles::$manager);
    }

    /** Whether a status counts as done in its project (status type DONE, which includes archived). */
    protected function isDone(int $projectId, mixed $status): bool
    {
        if (! isset($this->labels[$projectId])) {
            $this->labels[$projectId] = $this->tickets->getStateLabels($projectId);
        }

        return ($this->labels[$projectId][(int) $status]['statusType'] ?? '') === 'DONE';
    }

    /**
     * Whether the person's own role in the project holds the permission (tickets.view for to-dos
     * and milestones, projects.view for project ends), read without a session.
     */
    protected function mayView(int $userId, int $projectId, string $permission): bool
    {
        // One checker for this builder (one run), so its answers are reused across items.
        $this->recipientAccess ??= app()->make(RecipientAccess::class);

        return $this->recipientAccess->can($userId, $projectId, $permission);
    }

    /** Whether the person can still see the project (team, company-wide role, or shared with everyone). */
    protected function canSee(int $userId, int $projectId): bool
    {
        $key = $userId.'|'.$projectId;

        return $this->access[$key] ??= $this->projects->isUserAssignedToProject($userId, $projectId);
    }

    /**
     * @return array{kind: string, id: int, title: string, projectId: int, projectName: string, due: string, daysUntil: int, url: string}
     */
    /**
     * @param  bool  $isYours  False for someone else's to-do the person asked to be reminded about.
     */
    private function item(string $kind, int $id, string $title, int $projectId, string $projectName, string $dueUtc, int $daysUntil, string $path, bool $isYours = true): array
    {
        return [
            'kind' => $kind,
            'id' => $id,
            'title' => trim(strip_tags($title)),
            'projectId' => $projectId,
            'projectName' => trim(strip_tags($projectName)),
            'due' => CarbonImmutable::parse($dueUtc, 'UTC')->toIso8601ZuluString(),
            'daysUntil' => $daysUntil,
            'url' => rtrim($this->trustedAppUrl->forLinks(), '/').$path,
            'isYours' => $isYours,
        ];
    }

    private function localDayIndex(string $utc, string $timezone): int
    {
        return self::dayIndex(CarbonImmutable::parse($utc, 'UTC')->setTimezone($timezone)->toDateString());
    }

    private static function isDate(mixed $value): bool
    {
        return ReminderRepository::isRealDate($value);
    }

    private static function utc(CarbonImmutable $time): string
    {
        return $time->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}
