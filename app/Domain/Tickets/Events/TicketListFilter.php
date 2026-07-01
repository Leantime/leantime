<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the grouped ticket list before it is handed to the list/table templates.
 * Listeners receive the grouped tickets array and must return it (possibly modified).
 */
final class TicketListFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array  $tickets  The grouped tickets to filter.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site ran under for plugin listeners.
     */
    public function __construct(
        public array $tickets,
        private readonly ?string $legacyHook = null,
    ) {}

    /**
     * The grouped tickets array threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->tickets;
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

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.filterTickets'];
    }
}
