<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired when a milestone is created.
 */
final class MilestoneCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int|null  $milestoneId  The created milestone id; null when the emit site doesn't capture it.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site fired under for plugin listeners.
     */
    /**
     * @param  int|null  $milestoneId  The created milestone id; null when the write failed.
     * @param  string|null  $origin  Where it was created: 'quickadd', 'onboarding_seed', 'wizard',
     *                               'strategy', 'mcp', ... (same keys as {@see TicketCreated::$origin}).
     * @param  int|null  $projectId  The project the milestone was created in.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name.
     */
    public function __construct(
        public readonly ?int $milestoneId = null,
        public readonly ?string $origin = null,
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

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.milestone_created'];
    }
}
