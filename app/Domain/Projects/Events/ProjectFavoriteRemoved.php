<?php

namespace Leantime\Domain\Projects\Events;

use Leantime\Core\Events\Concerns\InteractsWithClientEvents;
use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after the session user removed a project from their favorites.
 *
 * On htmx requests it tells the browser lt:projects:favorite.removed, so project lists re-sort their favorites.
 */
final class ProjectFavoriteRemoved implements EmitsClientEvent, LeantimeEvent
{
    use InteractsWithClientEvents, InteractsWithEvents;

    public const CLIENT_EVENT = 'lt:projects:favorite.removed';

    /**
     * @param  int  $projectId  The project.
     * @param  int  $userId  The user whose favorites changed.
     */
    public function __construct(
        public readonly int $projectId,
        public readonly int $userId,
    ) {}

    /**
     * Scope the client event to the project.
     */
    public function clientScope(): int
    {
        return $this->projectId;
    }
}
