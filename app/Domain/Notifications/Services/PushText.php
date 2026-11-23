<?php

namespace Leantime\Domain\Notifications\Services;

use Illuminate\Support\Facades\DB;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\NameSanitizer;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * PushText — the words a project notification shows on a phone's lock screen.
 *
 * A notification's subject and message are written for email ("One of your To-Dos was
 * updated [Sales] - Pricing page" / "Marcel updated the To-Do: 'Pricing page'"), which is
 * hard to read in a notification. A push says the same thing the way the assignment push
 * does: the item as the title, who did what as the body.
 *
 *   Pricing page                       Pricing page
 *   Marcel updated this task · Sales   Marcel: Can we ship this Friday?
 *
 * A sender that knows more sets Notification::$pushTitle / $pushBody ("Marcel moved this
 * to Done"); those win. Anything this doesn't recognise keeps the email wording, so no
 * notification loses its push.
 *
 * At the "names" content level (Company Settings) a push keeps who did what and the item's
 * name but no comment or description text: "Marcel commented · Sales". The email wording
 * can carry that text, so at that level it is never used.
 */
class PushText
{
    /** Comment text longer than this is cut on a word boundary; the phone trims further. */
    private const SNIPPET_LENGTH = 140;

    /** The email digest quotes more of a comment than a lock screen can show. */
    private const QUOTE_LENGTH = 600;

    /** module → the noun in "<actor> updated this <noun>" (language key suffix). */
    private const ITEM_KINDS = [
        'tickets' => 'task',
        'goalcanvas' => 'goal',
        'ideas' => 'idea',
        'projects' => 'project',
    ];

    public function __construct(
        private LanguageCore $language,
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private TicketRepository $ticketRepository,
    ) {}

    /**
     * @return array{title: string, body: string}
     */
    public function forNotification(NotificationModel $notification, string $level = PushRelay::CONTENT_FULL): array
    {
        $namesOnly = $level === PushRelay::CONTENT_NAMES;
        $fallback = [
            'title' => $notification->subject !== '' ? $notification->subject : 'Leantime',
            'body' => $namesOnly ? $this->language->__('notification.push_names_fallback') : $notification->message,
        ];

        try {
            $built = $this->build($notification, $namesOnly);
        } catch (\Throwable) {
            $built = null;
        }

        $title = $notification->pushTitle ?? $built['title'] ?? null;
        $body = $built['body'] ?? $notification->pushBody;

        return [
            'title' => $title !== null && $title !== '' ? $title : $fallback['title'],
            'body' => $body !== null && $body !== '' ? $body : $fallback['body'],
        ];
    }

    /**
     * The same wording for the email digest, in parts: the item it is about ('group', so the digest
     * can put several changes to one task together), its name, "who did what", and a comment's
     * text to quote. Null when this can't word the notification; the digest then shows the
     * email's own subject and message.
     *
     * @return array{group: string, title: string, line: string, quote: string}|null
     */
    public function forEmail(NotificationModel $notification): ?array
    {
        try {
            $module = (string) ($notification->module ?? '');
            $action = $notification->action;
            $actor = $this->actorName((int) ($notification->authorId ?? 0));

            if ($module === 'comments' || $action === 'commented') {
                $host = $this->commentHost($notification);
                if ($host === null || $this->inOtherProject($host, $notification)) {
                    return null;
                }
                $comment = $this->comment($notification, $actor, true);
                if ($comment === null) {
                    return null;
                }

                return [
                    // The same key the item's own changes use, so they sit together.
                    'group' => $host['module'].':'.$host['id'],
                    'title' => $comment['title'],
                    'line' => sprintf($this->language->__('notification.push_commented_names'), $actor),
                    'quote' => $this->snippet((string) ($this->field($notification->entity, 'text') ?? ''), self::QUOTE_LENGTH),
                ];
            }

            $kind = self::ITEM_KINDS[$module] ?? (str_ends_with($module, 'canvas') ? 'board_item' : null);
            $id = (int) ($this->field($notification->entity, 'id') ?? 0);
            if ($kind === null || $id <= 0 || ! in_array($action, ['created', 'updated', 'status_changed', 'due_date_changed'], true)) {
                return null;
            }

            $title = $this->itemName($module, $notification->entity);
            if ($title === '') {
                return null;
            }

            $verb = $action === 'created' ? 'created' : 'updated';

            return [
                'group' => $module.':'.$id,
                'title' => $title,
                'line' => $notification->pushBody ?? sprintf($this->language->__('notification.push_'.$kind.'_'.$verb), $actor),
                'quote' => '',
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{title: string, body: string}|null
     */
    private function build(NotificationModel $notification, bool $namesOnly): ?array
    {
        $module = (string) ($notification->module ?? '');
        $action = $notification->action;
        $actor = $this->actorName((int) ($notification->authorId ?? 0));

        if ($module === 'comments' || $action === 'commented') {
            return $this->comment($notification, $actor, $namesOnly);
        }

        $kind = self::ITEM_KINDS[$module] ?? (str_ends_with($module, 'canvas') ? 'board_item' : null);
        if ($kind === null || ! in_array($action, ['created', 'updated', 'status_changed', 'due_date_changed'], true)) {
            return null;
        }

        $title = $this->itemName($module, $notification->entity);
        if ($title === '') {
            return null;
        }

        // The sender's own line ("Marcel moved this to Done") or, failing that, created/updated.
        $verb = $action === 'created' ? 'created' : 'updated';
        $body = $notification->pushBody ?? sprintf($this->language->__('notification.push_'.$kind.'_'.$verb), $actor);

        return ['title' => $title, 'body' => $this->withProject($body, $notification, $module)];
    }

    /**
     * A comment: the commented item as the title, "<actor>: <comment>" as the body.
     *
     * @return array{title: string, body: string}|null
     */
    private function comment(NotificationModel $notification, string $actor, bool $namesOnly): ?array
    {
        $text = $this->snippet((string) ($this->field($notification->entity, 'text') ?? ''));
        if ($text === '' && ! $namesOnly) {
            return null;
        }

        $host = $this->commentHost($notification);
        // An item of another project than the one being told: neither its name nor the comment
        // goes out. The email wording, which quotes neither, is used instead.
        if ($host !== null && $this->inOtherProject($host, $notification)) {
            return null;
        }

        $title = $host['name'] ?? '';

        if ($title === '') {
            $title = $this->language->__('notification.push_new_comment');
        }

        if ($namesOnly) {
            return [
                'title' => $title,
                'body' => $this->withProject(sprintf($this->language->__('notification.push_commented_names'), $actor), $notification, 'comments'),
            ];
        }

        return [
            'title' => $title,
            'body' => sprintf($this->language->__('notification.push_commented'), $actor, $text),
        ];
    }

    /**
     * The item a comment is on: its module, as that item's own notifications use it (so the
     * digest puts them together), its id, its name and the project it belongs to (0 when
     * unknown). Task and project comments say their host in the comment; goal and board
     * comments carry only the host id, with the module on the notification. Null when the
     * host can't be told.
     *
     * @return array{module: string, id: int, name: string, projectId: int}|null
     */
    private function commentHost(NotificationModel $notification): ?array
    {
        $id = (int) ($this->field($notification->entity, 'moduleId') ?? 0);
        $host = (string) ($this->field($notification->entity, 'module') ?? '');
        if ($host === '' && ($notification->module ?? '') !== 'comments') {
            $host = (string) $notification->module;
        }

        $module = match (true) {
            in_array($host, ['ticket', 'tickets'], true) => 'tickets',
            in_array($host, ['project', 'projects'], true) => 'projects',
            in_array($host, ['idea', 'ideas'], true) => 'ideas',
            str_ends_with($host, 'canvasitem') => substr($host, 0, -strlen('item')),
            str_ends_with($host, 'canvas') => $host,
            default => '',
        };
        if ($module === '' || $id <= 0) {
            return null;
        }

        if ($module === 'tickets') {
            $ticket = $this->ticketRepository->getTicket($id);
            $name = $ticket !== false ? (string) $ticket->headline : '';
            $projectId = $ticket !== false ? (int) ($ticket->projectId ?? 0) : 0;
        } elseif ($module === 'projects') {
            $project = $this->projectRepository->getProject($id);
            $name = is_array($project) ? (string) ($project['name'] ?? '') : '';
            $projectId = is_array($project) ? $id : 0;
        } else {
            $item = $this->canvasItem($id);
            $name = $this->itemName($module, $item);
            $projectId = (int) ($item['projectId'] ?? 0);
        }

        return ['module' => $module, 'id' => $id, 'name' => $this->clean($name), 'projectId' => $projectId];
    }

    /**
     * Whether the commented item belongs to another project than the notification's. Goals,
     * ideas and board items share one id sequence (zp_canvas_items), so a bare id can name
     * another project's item: its name and the comment then stay out of what this project's
     * team is told. Unknown on either side counts as not other.
     *
     * @param  array{projectId: int}  $host
     */
    private function inOtherProject(array $host, NotificationModel $notification): bool
    {
        $notificationProjectId = (int) ($notification->projectId ?? 0);

        return $host['projectId'] > 0 && $notificationProjectId > 0 && $host['projectId'] !== $notificationProjectId;
    }

    /**
     * A goal, idea or board item by id (they share zp_canvas_items) with the project of its
     * board, or null.
     *
     * @return array{title: ?string, description: ?string, projectId: int|string|null}|null
     */
    protected function canvasItem(int $id): ?array
    {
        $row = DB::table('zp_canvas_items')
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->where('zp_canvas_items.id', $id)
            ->first(['zp_canvas_items.title', 'zp_canvas_items.description', 'zp_canvas.projectId']);

        return $row !== null ? (array) $row : null;
    }

    /**
     * An item's name. Goals keep it in title and their details in description, so a goal is
     * named by its title only: the details never stand in for a name. Ideas and board items
     * keep their name in description (title is a rarely-used extra).
     */
    private function itemName(string $module, mixed $entity): string
    {
        $fields = match ($module) {
            'tickets' => ['headline'],
            'projects' => ['name'],
            'goalcanvas' => ['title'],
            default => ['description', 'title'],
        };

        foreach ($fields as $field) {
            $name = $this->clean((string) ($this->field($entity, $field) ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    /** "<body> · <project>", when the project has a name. */
    private function withProject(string $body, NotificationModel $notification, string $module): string
    {
        $projectId = (int) ($notification->projectId ?? 0);
        if ($module === 'projects' || $projectId <= 0) {
            return $body;
        }

        $project = $this->projectRepository->getProject($projectId);
        $name = is_array($project) ? $this->clean((string) ($project['name'] ?? '')) : '';

        return $name !== '' ? $body.' · '.$name : $body;
    }

    private function actorName(int $actorId): string
    {
        $actor = $actorId > 0 ? $this->userRepository->getUser($actorId) : false;
        $name = $actor === false ? '' : NameSanitizer::clean($actor['firstname'] ?? '');

        return $name !== '' ? $name : $this->language->__('label.someone');
    }

    private function snippet(string $html, int $length = self::SNIPPET_LENGTH): string
    {
        $text = $this->clean($html);
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $cut = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false && $space > $length / 2 ? mb_substr($cut, 0, $space) : $cut).'…';
    }

    /** Plain text on one line: tags and entities out, whitespace collapsed. */
    private function clean(string $value): string
    {
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /** Reads a field from an entity that is an array (legacy) or an object (newer models). */
    private function field(mixed $entity, string $key): mixed
    {
        if (is_array($entity)) {
            return $entity[$key] ?? null;
        }
        if (is_object($entity)) {
            return $entity->{$key} ?? null;
        }

        return null;
    }
}
