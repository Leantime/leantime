<?php

namespace Leantime\Domain\Notifications\Services;

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
                $host = (string) ($this->field($notification->entity, 'module') ?? '');
                $hostId = (int) ($this->field($notification->entity, 'moduleId') ?? 0);
                $comment = $this->comment($notification, $actor, true);
                if ($comment === null || $hostId <= 0) {
                    return null;
                }

                return [
                    'group' => (in_array($host, ['ticket', 'tickets'], true) ? 'tickets' : $host).':'.$hostId,
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

        $title = '';
        $host = (string) ($this->field($notification->entity, 'module') ?? '');
        $hostId = (int) ($this->field($notification->entity, 'moduleId') ?? 0);
        if (in_array($host, ['ticket', 'tickets'], true) && $hostId > 0) {
            $ticket = $this->ticketRepository->getTicket($hostId);
            $title = $ticket !== false ? $this->clean((string) $ticket->headline) : '';
        } elseif (in_array($host, ['project', 'projects'], true) && $hostId > 0) {
            $project = $this->projectRepository->getProject($hostId);
            $title = is_array($project) ? $this->clean((string) ($project['name'] ?? '')) : '';
        } else {
            // A board item, goal or idea comment carries the item itself.
            $title = $this->clean((string) ($this->field($notification->entity, 'description') ?? ''));
        }

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

    private function itemName(string $module, mixed $entity): string
    {
        $field = match ($module) {
            'tickets' => 'headline',
            'projects' => 'name',
            default => 'description', // goals, ideas and board items keep their title here
        };

        return $this->clean((string) ($this->field($entity, $field) ?? ''));
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
