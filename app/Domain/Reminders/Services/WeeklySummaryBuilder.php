<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Recap\Services\Recap;
use Leantime\Domain\Reminders\Events\WeeklySummarySectionsFilter;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Support\WorkStart;

/**
 * One person's week: what's ahead in the next seven local days (today included), a gentle look
 * back at the seven days before, and sections plugins add. The one builder the weekly email,
 * push and inbox row and the API all use. It follows the morning summary's rules for what is
 * theirs and what they may see ({@see SummaryBuilder}).
 *
 * This week (ahead):
 *  - To-dos due in the seven days: theirs, plus anyone else's they asked to be reminded about.
 *    A to-do whose reminders they turned off is left out. The first {@see MAX_ITEMS} are listed
 *    by day; the rest are "and N more". To-dos that repeat every weekday or more often are one
 *    routine line, never listed.
 *  - Milestones they own and projects they get end notices for, ending in the seven days.
 *  - Planned days: their open to-dos with a Work Start on each day (core's {@see WorkStart}
 *    rules), counted per day, with how many of those have a set time.
 *  - "N still open from earlier": one count, never a list; alone it never sends a summary.
 *
 * Last week (look back), when the person keeps it on: the to-dos they finished in the seven days
 * before today ({@see Recap::finishedBetweenFor()}), counted, with up to {@see LOOK_BACK_NAMES}
 * named. Left out when nothing was finished: never a zero, never a comparison.
 *
 * Left out on purpose: tasks to consider, quick wins and "your one thing" (daily nudges).
 *
 * Every section has a stable kind ('today', 'ahead', 'lookBack', 'plugin:{plugin}.{section}')
 * and items share the morning summary's item shape.
 */
class WeeklySummaryBuilder extends SummaryBuilder
{
    /** How many local days "this week" covers, today included. */
    public const DAYS = 7;

    /** How many finished to-dos the look-back names. */
    public const LOOK_BACK_NAMES = 3;

    /** The most lines a plugin section shows. */
    public const MAX_PLUGIN_LINES = 5;

    public function __construct(
        ReminderRepository $reminders,
        TicketRepository $tickets,
        ProjectRepository $projects,
        TrustedAppUrl $trustedAppUrl,
        private Recap $recap,
    ) {
        parent::__construct($reminders, $tickets, $projects, $trustedAppUrl);
    }

    /**
     * @param  CarbonImmutable  $nowUtc  The current time.
     * @param  array{taskPreset: string, timezone: string, weeklyLookBack: bool}  $mine  The person's settings.
     * @param  array<string, mixed>|null  $today  Today's morning summary ({@see SummaryBuilder::build()}) when the
     *                                            weekly summary includes it; null otherwise.
     * @param  int  $limit  How many items "This week" lists; 0 lists them all.
     * @return array{
     *     week: array{start: string, end: string},
     *     today: array<string, mixed>|null,
     *     ahead: array<string, mixed>,
     *     lookBack: array<string, mixed>|null,
     *     sections: array<int, array<string, mixed>>,
     *     isEmpty: bool
     * }
     */
    public function buildWeek(int $userId, CarbonImmutable $nowUtc, array $mine, ?array $today = null, int $limit = self::MAX_ITEMS): array
    {
        $timezone = $mine['timezone'];
        $start = $nowUtc->setTimezone($timezone)->startOfDay();
        $last = $start->addDays(self::DAYS - 1);

        $ahead = $this->ahead($userId, $start, $timezone, $mine['taskPreset'], $limit);
        $lookBack = $mine['weeklyLookBack'] ? $this->lookBack($userId, $start) : null;
        $plugins = $this->pluginSections($userId, $start->toDateString(), $last->toDateString(), $timezone);

        $todaySection = $today !== null && ! ($today['isEmpty'] ?? true) ? ['kind' => 'today'] + $today : null;

        $aheadHasSomething = $ahead['items'] !== [] || $ahead['routine']['count'] > 0 || $ahead['plannedCount'] > 0;
        $pluginCounts = array_filter($plugins, fn (array $section): bool => $section['countsForSending']) !== [];

        return [
            'week' => ['start' => $start->toDateString(), 'end' => $last->toDateString()],
            'today' => $todaySection,
            'ahead' => $ahead,
            'lookBack' => $lookBack,
            'sections' => array_values(array_filter([$todaySection, $ahead, $lookBack, ...$plugins])),
            'isEmpty' => ! $aheadHasSomething && $lookBack === null && ! $pluginCounts,
        ];
    }

    /**
     * @return array{kind: string, items: array<int, array<string, mixed>>, moreCount: int, dueCount: int, endingCount: int, planned: array<int, array{date: string, count: int, timeBoxed: int}>, plannedCount: int, routine: array{count: int}, overdueCount: int}
     */
    private function ahead(int $userId, CarbonImmutable $start, string $timezone, string $defaultPreset, int $limit): array
    {
        $startIndex = self::dayIndex($start->toDateString());
        $endOfWeek = $start->addDays(self::DAYS - 1)->endOfDay();
        $overdueCount = 0;
        $routineIds = [];

        $entries = array_merge(
            $this->weekTaskEntries($userId, $start, $endOfWeek, $startIndex, $timezone, $defaultPreset, $overdueCount, $routineIds),
            $this->weekMilestoneEntries($userId, $start, $endOfWeek, $startIndex, $timezone),
            $this->weekProjectEntries($userId, $start, $endOfWeek, $startIndex, $timezone),
        );

        usort($entries, static fn (array $a, array $b) => [$a['item']['daysUntil'], $a['rank'], $a['priority'], $a['item']['title'], $a['item']['id']]
            <=> [$b['item']['daysUntil'], $b['rank'], $b['priority'], $b['item']['title'], $b['item']['id']]);
        $items = array_map(fn (array $entry) => $entry['item'], $entries);

        [$planned, $plannedCount] = $this->planned($userId, $start, $endOfWeek, $timezone);

        $tasks = count(array_filter($items, fn (array $item): bool => $item['kind'] === 'task'));

        return [
            'kind' => 'ahead',
            'items' => $limit > 0 ? array_slice($items, 0, $limit) : $items,
            'moreCount' => $limit > 0 ? max(0, count($items) - $limit) : 0,
            'dueCount' => $tasks + count($routineIds),
            'endingCount' => count($items) - $tasks,
            'planned' => $planned,
            'plannedCount' => $plannedCount,
            'routine' => ['count' => count($routineIds)],
            'overdueCount' => $overdueCount,
        ];
    }

    /**
     * To-dos due in the week. Routines are only counted ($routineIds), overdue ones only counted.
     *
     * @param  array<int, true>  $routineIds
     * @return array<int, array{item: array<string, mixed>, rank: int, priority: int}>
     */
    private function weekTaskEntries(int $userId, CarbonImmutable $start, CarbonImmutable $endOfWeek, int $startIndex, string $timezone, string $defaultPreset, int &$overdueCount, array &$routineIds): array
    {
        $chosen = array_filter($this->reminders->taskChoices($userId), fn (string $preset) => in_array($preset, ReminderSettings::PRESETS, true));
        $optedIn = array_keys(array_filter($chosen, fn (string $preset) => $preset !== ReminderSettings::PRESET_OFF));

        $rows = $this->reminders->tasksFor($userId, self::utc($start), self::utc($start->endOfDay()), self::utc($endOfWeek), $optedIn);
        $rows = array_values(array_filter($rows, fn (array $row) => ! $this->isDone((int) $row['projectId'], $row['status'])
            && $this->canSee($userId, (int) $row['projectId'])
            && $this->mayView($userId, (int) $row['projectId'], TicketsPermissions::VIEW)));

        $frequent = $this->reminders->frequentRecurring(array_map(fn (array $row) => (int) $row['id'], $rows));

        $entries = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $isRoutine = isset($frequent[$id]);
            $isYours = (bool) ($row['isYours'] ?? true);
            if (ReminderSettings::effectivePreset($chosen[$id] ?? null, $defaultPreset, $isRoutine, $isYours) === ReminderSettings::PRESET_OFF) {
                continue;
            }

            $daysUntil = $this->localDayIndex((string) $row['dateToFinish'], $timezone) - $startIndex;
            if ($daysUntil < 0) {
                $overdueCount++;

                continue;
            }
            if ($daysUntil >= self::DAYS) {
                continue;
            }
            if ($isRoutine) {
                $routineIds[$id] = true;

                continue;
            }

            $entries[] = [
                'item' => $this->item('task', $id, (string) $row['headline'], (int) $row['projectId'], (string) ($row['projectName'] ?? ''), (string) $row['dateToFinish'], $daysUntil, '/dashboard/home#/tickets/showTicket/'.$id, $isYours),
                'rank' => 2,
                'priority' => is_numeric($row['priority'] ?? null) && (int) $row['priority'] > 0 ? (int) $row['priority'] : 99,
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array{item: array<string, mixed>, rank: int, priority: int}>
     */
    private function weekMilestoneEntries(int $userId, CarbonImmutable $start, CarbonImmutable $endOfWeek, int $startIndex, string $timezone): array
    {
        $entries = [];
        foreach ($this->reminders->milestonesFor($userId, self::utc($start), self::utc($endOfWeek)) as $row) {
            if ($this->isDone((int) $row['projectId'], $row['status']) || ! $this->canSee($userId, (int) $row['projectId'])
                || ! $this->mayView($userId, (int) $row['projectId'], TicketsPermissions::VIEW)) {
                continue;
            }

            $id = (int) $row['id'];
            $entries[] = [
                'item' => $this->item('milestone', $id, (string) $row['headline'], (int) $row['projectId'], (string) ($row['projectName'] ?? ''), (string) $row['editTo'], $this->localDayIndex((string) $row['editTo'], $timezone) - $startIndex, '/dashboard/home#/tickets/editMilestone/'.$id),
                'rank' => 1,
                'priority' => 0,
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array{item: array<string, mixed>, rank: int, priority: int}>
     */
    private function weekProjectEntries(int $userId, CarbonImmutable $start, CarbonImmutable $endOfWeek, int $startIndex, string $timezone): array
    {
        $entries = [];
        foreach ($this->reminders->projectsEndingFor($userId, self::utc($start), self::utc($endOfWeek)) as $row) {
            if (! in_array($userId, $this->endRecipients($row['id']), true) || ! $this->mayView($userId, $row['id'], ProjectsPermissions::VIEW)) {
                continue;
            }

            $entries[] = [
                'item' => $this->item('project', $row['id'], $row['name'], $row['id'], $row['name'], $row['end'], $this->localDayIndex($row['end'], $timezone) - $startIndex, '/projects/changeCurrentProject/'.$row['id']),
                'rank' => 0,
                'priority' => 0,
            ];
        }

        return $entries;
    }

    /**
     * Open to-dos planned on each day of the week, with how many of them have a set time.
     *
     * @return array{0: array<int, array{date: string, count: int, timeBoxed: int}>, 1: int} The days that have any, and how many to-dos in all.
     */
    private function planned(int $userId, CarbonImmutable $start, CarbonImmutable $endOfWeek, string $timezone): array
    {
        $days = [];
        for ($day = 0; $day < self::DAYS; $day++) {
            $days[$start->addDays($day)->toDateString()] = ['count' => 0, 'timeBoxed' => 0];
        }

        $tasks = [];
        foreach ($this->reminders->plannedTasksFor($userId, self::utc($start), self::utc($endOfWeek)) as $row) {
            if ($this->isDone($row['projectId'], $row['status']) || ! $this->canSee($userId, $row['projectId'])
                || ! $this->mayView($userId, $row['projectId'], TicketsPermissions::VIEW)) {
                continue;
            }

            $timeBoxed = WorkStart::isTimeBoxed($row['editFrom'], $row['editTo'], $timezone);
            foreach (WorkStart::plannedDays($row['editFrom'], $row['editTo'], $timezone) as $date) {
                if (! isset($days[$date])) {
                    continue;
                }
                $days[$date]['count']++;
                $days[$date]['timeBoxed'] += $timeBoxed ? 1 : 0;
                $tasks[$row['id']] = true;
            }
        }

        $planned = [];
        foreach ($days as $date => $counts) {
            if ($counts['count'] > 0) {
                $planned[] = ['date' => $date] + $counts;
            }
        }

        return [$planned, count($tasks)];
    }

    /**
     * The to-dos finished in the seven local days before today, or null when there are none.
     *
     * @return array{kind: string, start: string, end: string, count: int, items: array<int, array<string, mixed>>}|null
     */
    private function lookBack(int $userId, CarbonImmutable $start): ?array
    {
        $from = $start->subDays(self::DAYS);
        $finished = $this->recap->finishedBetweenFor($userId, $from->setTimezone('UTC'), $start->subSecond()->setTimezone('UTC'));
        if ($finished === []) {
            return null;
        }

        $base = rtrim($this->trustedAppUrl->forLinks(), '/');

        return [
            'kind' => 'lookBack',
            'start' => $from->toDateString(),
            'end' => $start->subDay()->toDateString(),
            'count' => count($finished),
            'items' => array_map(fn (array $row): array => [
                'kind' => 'task',
                'id' => $row['taskId'],
                'title' => $row['headline'],
                'projectId' => $row['projectId'],
                'projectName' => $row['projectName'],
                'url' => $base.'/dashboard/home#/tickets/showTicket/'.$row['taskId'],
                'isYours' => true,
                'milestoneId' => $row['milestoneId'],
                'finishedAt' => $row['finishedAt'],
                'closedBy' => $row['closedBy'],
            ], array_slice($finished, 0, self::LOOK_BACK_NAMES)),
        ];
    }

    /**
     * Sections from plugins ({@see WeeklySummarySectionsFilter}), checked and cleaned. Each listener
     * runs on its own: one that fails (or returns something else than a list) is logged and
     * skipped, and the sections the others added are kept. Core's filter pipeline stops at the
     * first failure, so the listeners are called here.
     *
     * @return array<int, array{kind: string, title: string, lines: array<int, string>, url: string|null, countsForSending: bool}>
     */
    private function pluginSections(int $userId, string $weekStart, string $weekEnd, string $timezone): array
    {
        $filter = new WeeklySummarySectionsFilter(sections: [], userId: $userId, weekStart: $weekStart, weekEnd: $weekEnd, timezone: $timezone);
        $listeners = EventDispatcher::findEventListeners(WeeklySummarySectionsFilter::class, EventDispatcher::getFilterRegistry());
        usort($listeners, fn (array $a, array $b): int => ($a['priority'] ?? 10) <=> ($b['priority'] ?? 10));

        $raw = [];
        foreach ($listeners as $listener) {
            try {
                $result = self::listenerCallable($listener['listener'])($raw, $filter);
            } catch (\Throwable $e) {
                Log::warning('Weekly summary: a plugin section failed for user '.$userId.': '.$e->getMessage());

                continue;
            }
            if (is_array($result)) {
                $raw = $result;
            } else {
                Log::warning('Weekly summary: a plugin listener returned no list; its result is ignored.');
            }
        }

        $sections = [];
        $kinds = [];
        foreach ($raw as $section) {
            $clean = is_array($section) ? $this->cleanSection($section) : null;
            if ($clean === null || isset($kinds[$clean['kind']])) {
                continue;
            }
            $kinds[$clean['kind']] = true;
            $sections[] = $clean;
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array{kind: string, title: string, lines: array<int, string>, url: string|null, countsForSending: bool}|null
     */
    private function cleanSection(array $section): ?array
    {
        $kind = $section['kind'] ?? null;
        $title = is_string($section['title'] ?? null) ? trim(strip_tags($section['title'])) : '';
        if (! is_string($kind) || preg_match('/^plugin:[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $kind) !== 1 || $title === '') {
            return null;
        }

        $lines = [];
        foreach (is_array($section['lines'] ?? null) ? $section['lines'] : [] as $line) {
            if (is_scalar($line) && ($text = trim(strip_tags((string) $line))) !== '') {
                $lines[] = $text;
            }
        }

        return [
            'kind' => $kind,
            'title' => $title,
            'lines' => array_slice($lines, 0, self::MAX_PLUGIN_LINES),
            'url' => $this->sectionUrl($section['url'] ?? null),
            'countsForSending' => ($section['countsForSending'] ?? false) === true,
        ];
    }

    /**
     * A filter listener registration as a callable, the way core resolves class-based filter
     * listeners: a closure or callable as is, "Class@method" or [Class, method] through the
     * container, and a class name on its handle() (or __invoke()) method.
     */
    private static function listenerCallable(mixed $listener): callable
    {
        if (is_callable($listener) && ! is_string($listener) && ! is_array($listener)) {
            return $listener;
        }

        if (is_string($listener) && ! function_exists($listener)) {
            [$class, $method] = str_contains($listener, '@') ? explode('@', $listener, 2) : [$listener, 'handle'];
        } elseif (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
            [$class, $method] = [$listener[0], $listener[1] ?? 'handle'];
        } else {
            return $listener;
        }

        if (! method_exists($class, $method)) {
            $method = '__invoke';
        }

        return [app()->make($class), $method];
    }

    /** A path on this Leantime, or a full URL on it; anything else is dropped. */
    private function sectionUrl(mixed $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $base = rtrim($this->trustedAppUrl->forLinks(), '/');
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $base.$url;
        }

        return str_starts_with($url, $base.'/') ? $url : null;
    }
}
