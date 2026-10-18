<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithClientEvents;
use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a ticket (including subtasks) was updated.
 *
 * On htmx requests it also tells the browser (lt:tickets:ticket.updated, plus the
 * ticket-scoped name when a single ticket changed).
 */
final class TicketUpdated implements EmitsClientEvent, LeantimeEvent
{
    use InteractsWithClientEvents, InteractsWithEvents;

    public const CLIENT_EVENT = 'lt:tickets:ticket.updated';

    /**
     * @param  int|null  $ticketId  The updated ticket id; null for bulk updates (sorting,
     *                              kanban status+sorting) where no single id applies.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site fired under for plugin listeners.
     */
    public function __construct(
        public readonly ?int $ticketId = null,
        private readonly ?string $legacyHook = null,
    ) {}

    /**
     * Scope the client event to the updated ticket; bulk updates have no single ticket.
     */
    public function clientScope(): ?int
    {
        return $this->ticketId;
    }

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

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.ticket_updated'];
    }
}
