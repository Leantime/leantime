<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a milestone was updated.
 */
final class MilestoneUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $milestoneId  The updated milestone id.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site fired under for plugin listeners.
     */
    /**
     * @param  int  $milestoneId  The updated milestone id.
     * @param  int|null  $projectId  The project the milestone belongs to after the update.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name.
     */
    public function __construct(
        public readonly int $milestoneId,
        public readonly ?int $projectId = null,
        private readonly ?string $legacyHook = null,
    ) {}

    /**
     * The exact historical string name of the emitting site. Remove with the migration window.
     *
     * @return array<int, string>
     */
    public function legacyHooks(): array
    {
        if ($this->legacyHook === null) {
            return [];
        }

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.milestone_updated'];
    }
}
