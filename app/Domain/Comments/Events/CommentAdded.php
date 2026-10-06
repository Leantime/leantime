<?php

namespace Leantime\Domain\Comments\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a comment was stored on any entity (ticket, project, idea, canvas item, ...).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class CommentAdded implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $commentId  The id of the new comment.
     * @param  string  $module  The comment module (ticket, project, idea, {type}canvasitem, ...).
     * @param  int  $moduleId  The id of the commented entity.
     * @param  int|null  $projectId  The project the comment was authorized against, when known.
     * @param  bool  $isReply  Whether the comment replies to another comment.
     * @param  bool  $hasMention  Whether the comment @mentions a user.
     */
    public function __construct(
        public readonly int $commentId,
        public readonly string $module,
        public readonly int $moduleId,
        public readonly ?int $projectId,
        public readonly bool $isReply = false,
        public readonly bool $hasMention = false,
    ) {}
}
