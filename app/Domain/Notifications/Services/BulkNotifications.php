<?php

namespace Leantime\Domain\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Events\TicketAssigned;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * Holds to-do notifications during a bulk write, such as an import, and sends one summary
 * per project afterwards.
 *
 * Without this, every saved row notifies the whole team (inbox, email, push, webhooks) and
 * its assignee: 1,000 imported rows in a 20-person project queue about 19,000 emails.
 *
 * While held, Projects::notifyProjectUsers() records to-do notifications here instead of
 * sending them, and NotifyAssignee records assignments. When the outermost hold() ends, each
 * project that got open to-dos or milestones gets one notification. Done and archived items
 * are counted in it but don't cause one: a file of finished work isn't news.
 *
 * The summary goes through the normal project notification path, so everyone's preferences
 * apply (mute, categories, the email switch, the push switch). The assignees, creators and
 * collaborators of the open items count as involved in it, so it reaches people who follow
 * only their own work, on every channel they kept on. Collaborators keep the filter bypass a
 * per-row notification gives them.
 *
 * Only to-do (module 'tickets') notifications are held; others are sent as usual.
 */
class BulkNotifications
{
    /** How many hold() calls are running; nested holds send once, at the end of the outermost. */
    private static int $depth = 0;

    /**
     * What was saved while held, per project and item.
     *
     * @var array<int, array<int, array{action: string, status: mixed, type: string, people: array<int, int>, collaborators: array<int, int>, peopleKnown: bool}>>
     */
    private static array $items = [];

    /** @var array<int, int> Item id => id of the person it was assigned to. */
    private static array $assignments = [];

    /**
     * The services are only needed to send a summary, so they are resolved then: holding
     * notifications around an import that sends none needs no project, ticket or language
     * services (or a database). Tests pass stubs.
     */
    public function __construct(
        private ?ProjectService $projectService = null,
        private ?TicketRepository $ticketRepository = null,
        private ?LanguageCore $language = null,
        private ?ProjectRepository $projectRepository = null,
    ) {}

    /**
     * Runs $work with to-do notifications held, then sends the summaries.
     *
     * The summaries are sent even when $work throws, for the rows saved before it did.
     *
     * @template T
     *
     * @param  callable(): T  $work
     * @return T What $work returned.
     */
    public function hold(callable $work): mixed
    {
        self::$depth++;

        try {
            return $work();
        } finally {
            self::$depth--;

            if (self::$depth === 0) {
                $this->sendSummaries();
            }
        }
    }

    /**
     * Whether to-do notifications are being held.
     */
    public static function isHolding(): bool
    {
        return self::$depth > 0;
    }

    /**
     * Records a to-do notification instead of sending it.
     *
     * @return bool True when it was recorded (don't send it), false when nothing is held or it
     *              isn't a to-do notification (send it as usual).
     */
    public static function record(Notification $notification): bool
    {
        if (! self::isHolding() || $notification->module !== 'tickets') {
            return false;
        }

        $entity = (array) $notification->entity;
        $itemId = (int) ($entity['id'] ?? 0);
        $projectId = (int) $notification->projectId;

        // Without an item id or project there is nothing to summarise; the notification is dropped.
        if ($itemId <= 0 || $projectId <= 0) {
            return true;
        }

        $known = self::$items[$projectId][$itemId] ?? null;
        $collaborators = self::ids(is_array($entity['collaborators'] ?? null) ? $entity['collaborators'] : []);
        self::$items[$projectId][$itemId] = [
            // An item added and then changed in the same import counts as added.
            'action' => $known['action'] ?? (string) $notification->action,
            // An update that leaves the status out keeps the last one known.
            'status' => array_key_exists('status', $entity) ? $entity['status'] : ($known['status'] ?? null),
            'type' => strtolower((string) ($entity['type'] ?? $known['type'] ?? '')),
            // Assignee and creator as saved. When either is missing (an update's entity has no
            // creator, and may leave the assignee out), the stored ones are looked up when the
            // summary is sent.
            'people' => array_values(array_unique(array_merge($known['people'] ?? [], self::ids([$entity['editorId'] ?? null, $entity['userId'] ?? null]), $collaborators))),
            'collaborators' => array_values(array_unique(array_merge($known['collaborators'] ?? [], $collaborators))),
            'peopleKnown' => ($known['peopleKnown'] ?? false) || (array_key_exists('editorId', $entity) && array_key_exists('userId', $entity)),
        ];

        return true;
    }

    /**
     * Records an assignment instead of sending "assigned to you".
     *
     * @return bool True when it was recorded (don't send it), false when nothing is held.
     */
    public static function recordAssignment(TicketAssigned $event): bool
    {
        if (! self::isHolding()) {
            return false;
        }

        if (! $event->assignedToSelf) {
            self::$assignments[$event->ticketId] = $event->assigneeId;
        }

        return true;
    }

    /**
     * Sends one summary per project that got open items, and clears what was recorded.
     */
    private function sendSummaries(): void
    {
        $items = self::$items;
        $assignments = self::$assignments;
        self::$items = [];
        self::$assignments = [];

        if ($items === []) {
            return;
        }

        $this->projectService ??= app()->make(ProjectService::class);
        $this->projectRepository ??= app()->make(ProjectRepository::class);
        $this->ticketRepository ??= app()->make(TicketRepository::class);
        $this->language ??= app()->make(LanguageCore::class);

        foreach ($items as $projectId => $projectItems) {
            try {
                $this->sendSummary($projectId, $projectItems, $assignments);
            } catch (\Throwable $e) {
                // The rows are saved; a failed summary must not turn the import into an error.
                Log::warning('Import summary notification failed for project #'.$projectId.' ('.get_class($e).')');
            }
        }
    }

    /**
     * Sends the summary for one project, unless every item is done or archived.
     *
     * @param  array<int, array{action: string, status: mixed, type: string, people: array<int, int>, collaborators: array<int, int>, peopleKnown: bool}>  $projectItems
     * @param  array<int, int>  $assignments  Item id => assignee id.
     */
    private function sendSummary(int $projectId, array $projectItems, array $assignments): void
    {
        $openStatuses = [];
        foreach ($this->ticketRepository->getStateLabels($projectId) as $statusId => $label) {
            if (in_array($label['statusType'] ?? '', ['NEW', 'INPROGRESS'], true)) {
                $openStatuses[] = (string) $statusId;
            }
        }

        $open = 0;
        $closed = 0;
        $milestones = 0;
        $involved = [];
        $collaborators = [];
        foreach ($projectItems as $itemId => $item) {
            if ($item['type'] === 'milestone') {
                $milestones++;
            }

            // An unknown status (an update that didn't change it) counts as open, so nobody
            // misses an item they still have to work on.
            $isOpen = $item['status'] === null || in_array((string) $item['status'], $openStatuses, true);
            if (! $isOpen) {
                $closed++;

                continue;
            }

            $open++;
            $people = $item['people'];
            if (! $item['peopleKnown']) {
                $people = array_merge($people, $this->projectRepository->getTicketPeopleIds($itemId));
            }
            if (isset($assignments[$itemId])) {
                $people[] = $assignments[$itemId];
            }
            array_push($involved, ...$people);
            array_push($collaborators, ...$item['collaborators']);
        }

        if ($open === 0) {
            return;
        }

        $kind = $milestones === count($projectItems) ? 'milestones' : 'todos';
        $actor = (string) session('userdata.name');
        $projectName = (string) $this->projectService->getProjectName($projectId);

        $message = sprintf($this->language->__('email_notifications.import_'.$kind.'_message'), $actor, $open, $projectName);
        if ($closed > 0) {
            $message .= ' '.sprintf($this->language->__('email_notifications.import_closed_note'), $closed);
        }

        $notification = new Notification;
        $notification->module = 'tickets';
        $notification->action = 'imported';
        $notification->projectId = $projectId;
        $notification->authorId = session('userdata.id') ?? -1;
        $notification->subject = sprintf($this->language->__('email_notifications.import_'.$kind.'_subject'), $actor, $open, $projectName);
        $notification->message = $message;
        $notification->url = [
            'url' => BASE_URL.'/projects/changeCurrentProject/'.$projectId,
            'text' => $this->language->__('email_notifications.import_cta'),
        ];
        $notification->entity = [
            'projectId' => $projectId,
            // The per-row notifications let collaborators past the relevance and category filters.
            'collaborators' => array_values(array_unique($collaborators)),
        ];
        // The open items' people: they pass "My work" and get the push, as for their own item.
        $notification->involvedUserIds = array_values(array_diff(array_unique($involved), [(int) $notification->authorId]));

        $this->projectService->notifyProjectUsers($notification);
    }

    /**
     * Positive user ids from a list of values.
     *
     * @param  array<int, mixed>  $values
     * @return array<int, int>
     */
    private static function ids(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if (is_scalar($value) && is_numeric($value) && (int) $value > 0) {
                $ids[] = (int) $value;
            }
        }

        return $ids;
    }
}
