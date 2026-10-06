<?php

namespace Leantime\Domain\Blueprints\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a canvas board was created — for any canvas type (Blueprints boards, legacy Canvas paths, goal boards, idea boards), including copies and imports. Wiki notebooks do not fire this (see WikiArticleCreated); auto-created default boards do not fire either.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class CanvasCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $canvasId  The id of the new board.
     * @param  string  $type  The canvas database type (e.g. 'swotcanvas', 'goalcanvas', 'idea').
     * @param  int|null  $projectId  The board's project id, when known.
     */
    public function __construct(
        public readonly int $canvasId,
        public readonly string $type,
        public readonly ?int $projectId,
    ) {}
}
