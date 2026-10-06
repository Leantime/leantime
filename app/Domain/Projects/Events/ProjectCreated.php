<?php

namespace Leantime\Domain\Projects\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a new project was created (not on duplication — see the projectDuplicated string event).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class ProjectCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $projectId  The id of the created project.
     * @param  string  $type  The project type: 'project', 'strategy', 'program', ...
     */
    public function __construct(
        public readonly int $projectId,
        public readonly string $type,
    ) {}
}
