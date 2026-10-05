<?php

namespace Leantime\Domain\Projects\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a project was closed/archived (its state changed to -1 from any other state).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class ProjectArchived implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $projectId  The id of the archived project.
     */
    public function __construct(
        public readonly int $projectId,
    ) {}
}
