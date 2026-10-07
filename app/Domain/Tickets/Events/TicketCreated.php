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
     * @param  string|null  $origin  Where the ticket was created: 'quickadd', 'modal', 'subtask',
     *                               'todo_widget', 'import', 'mcp', 'onboarding' (typed by the user
     *                               during onboarding), 'onboarding_seed' (generated for them),
     *                               'wizard', 'strategy', ... (null when unknown).
     * @param  string|null  $type  The ticket type ('task', 'bug', 'subtask', ...).
     * @param  bool  $hasDueDate  Whether the ticket was created with a due date.
     * @param  bool  $assignedToOther  Whether it was created assigned to someone other than the creator.
     * @param  int|null  $projectId  The project the ticket was created in.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the exact historical
     *                                   string name this site fired under for plugin listeners.
     */
    public function __construct(
        public readonly ?int $ticketId = null,
        public readonly ?string $origin = null,
        public readonly ?string $type = null,
        public readonly bool $hasDueDate = false,
        public readonly bool $assignedToOther = false,
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

        return ['leantime.domain.tickets.services.tickets.'.$this->legacyHook.'.ticket_created'];
    }
}
