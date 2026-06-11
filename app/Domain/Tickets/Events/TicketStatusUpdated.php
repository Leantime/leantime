<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired from the repository when a ticket's status column changes (kanban moves,
 * inline patches). Carries the legacy payload keys (ticketId/status/action/handler)
 * that existing plugin listeners read.
 */
final class TicketStatusUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * Legacy payload discriminator kept for plugin listeners that switch on it.
     */
    public string $action = 'ticketStatusUpdate';

    /**
     * @param  int  $ticketId  The ticket whose status changed.
     * @param  mixed  $status  The new status value.
     * @param  mixed  $handler  Optional handler context passed by kanban updates.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site fired under for plugin listeners.
     */
    public function __construct(
        public readonly int $ticketId,
        public readonly mixed $status,
        public readonly mixed $handler = null,
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

        return ['leantime.domain.tickets.repositories.tickets.'.$this->legacyHook.'.ticketStatusUpdate'];
    }
}
