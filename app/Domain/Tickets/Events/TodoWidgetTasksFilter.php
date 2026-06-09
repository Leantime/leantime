<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the current user's tasks before the My-ToDos widget renders them.
 * Listeners receive the grouped tickets array and must return it (possibly modified).
 */
final class TodoWidgetTasksFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array  $tickets  The grouped widget tickets to filter.
     * @param  bool  $hierarchical  True when tickets are grouped hierarchically
     *                              (milestone/parent nesting) instead of flat groups.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site ran under for plugin listeners.
     */
    public function __construct(
        public array $tickets,
        public readonly bool $hierarchical = false,
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

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.myTodoWidgetTasks'];
    }
}
