<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a ticket (including subtasks) was created.
 */
final class TicketCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int|null  $ticketId  The created ticket id; null when the emit site doesn't capture it.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site fired under for plugin listeners.
     */
    public function __construct(
        public readonly ?int $ticketId = null,
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

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.ticket_created'];
    }
}
