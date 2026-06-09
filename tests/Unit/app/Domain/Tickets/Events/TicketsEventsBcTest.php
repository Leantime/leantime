<?php

namespace Unit\app\Domain\Tickets\Events;

use Codeception\Test\Unit;
use Leantime\Domain\Tickets\Events\MilestoneCreated;
use Leantime\Domain\Tickets\Events\MilestoneDeleted;
use Leantime\Domain\Tickets\Events\MilestoneUpdated;
use Leantime\Domain\Tickets\Events\StatusLabelsUpdated;
use Leantime\Domain\Tickets\Events\TicketCreated;
use Leantime\Domain\Tickets\Events\TicketDeleted;
use Leantime\Domain\Tickets\Events\TicketListFilter;
use Leantime\Domain\Tickets\Events\TicketStatusUpdated;
use Leantime\Domain\Tickets\Events\TicketUpdated;
use Leantime\Domain\Tickets\Events\TodoWidgetTasksFilter;

/**
 * Backwards-compatibility contract for the Tickets pilot: every migrated emit site must
 * keep producing the EXACT historical string name it fired under before the class-based
 * migration (audited 2026-06). Plugins (Copilot, Llamadorian, Reactions, RecurringTasks,
 * observability wildcards) subscribe to these strings — a mismatch silently orphans them.
 *
 * The expected names are frozen from the pre-migration audit. If this test fails, fix the
 * event class or call site — do NOT update the expected name unless the corresponding
 * legacy hook is being intentionally retired at the end of the migration window.
 */
class TicketsEventsBcTest extends Unit
{
    public function test_every_migrated_emit_site_produces_its_audited_historical_name(): void
    {
        $prefix = 'leantime.domain.tickets.services.tickets.';
        $repoPrefix = 'leantime.domain.tickets.repositories.tickets.';

        $expectations = [
            // events — services
            [new TicketCreated(ticketId: 1, legacyHook: 'quickAddTicket'), $prefix.'quickAddTicket.ticket_created'],
            [new TicketCreated(ticketId: 1, legacyHook: 'addTicket'), $prefix.'addTicket.ticket_created'],
            [new TicketCreated(legacyHook: 'upsertSubtask'), $prefix.'upsertSubtask.ticket_created'],
            [new TicketUpdated(ticketId: 1, legacyHook: 'updateTicket'), $prefix.'updateTicket.ticket_updated'],
            [new TicketUpdated(ticketId: 1, legacyHook: 'patch'), $prefix.'patch.ticket_updated'],
            [new TicketUpdated(ticketId: 1, legacyHook: 'upsertSubtask'), $prefix.'upsertSubtask.ticket_updated'],
            [new TicketUpdated(legacyHook: 'updateTicketSorting'), $prefix.'updateTicketSorting.ticket_updated'],
            [new TicketUpdated(legacyHook: 'updateTicketStatusAndSorting'), $prefix.'updateTicketStatusAndSorting.ticket_updated'],
            [new TicketDeleted(ticketId: 1, legacyHook: 'delete'), $prefix.'delete.ticket_deleted'],
            [new MilestoneCreated(legacyHook: 'quickAddMilestone'), $prefix.'quickAddMilestone.milestone_created'],
            [new MilestoneUpdated(milestoneId: 1, legacyHook: 'quickUpdateMilestone'), $prefix.'quickUpdateMilestone.milestone_updated'],
            [new MilestoneDeleted(milestoneId: 1, legacyHook: 'deleteMilestone'), $prefix.'deleteMilestone.milestone_deleted'],
            [new StatusLabelsUpdated(projectId: 1, legacyHook: 'saveStatusLabels'), $prefix.'saveStatusLabels.statusLabels_updated'],
            // events — repository
            [new TicketStatusUpdated(ticketId: 1, status: 3, legacyHook: 'patchTicket'), $repoPrefix.'patchTicket.ticketStatusUpdate'],
            [new TicketStatusUpdated(ticketId: 1, status: 3, legacyHook: 'updateTicketStatus'), $repoPrefix.'updateTicketStatus.ticketStatusUpdate'],
            // filters
            [new TicketListFilter(tickets: [], legacyHook: 'getTicketTemplateAssignments'), $prefix.'getTicketTemplateAssignments.filterTickets'],
            [new TodoWidgetTasksFilter(tickets: [], legacyHook: 'getToDoWidgetAssignments'), $prefix.'getToDoWidgetAssignments.myTodoWidgetTasks'],
            [new TodoWidgetTasksFilter(tickets: [], hierarchical: true, legacyHook: 'getToDoWidgetHierarchicalAssignments'), $prefix.'getToDoWidgetHierarchicalAssignments.myTodoWidgetTasks'],
        ];

        foreach ($expectations as [$event, $expectedName]) {
            $this->assertSame(
                [$expectedName],
                $event->legacyHooks(),
                get_class($event).' must keep firing its audited historical name'
            );
        }
    }

    /**
     * Each emit site passes __FUNCTION__ as the legacy hook, so the method names baked
     * into the expectations above must actually exist on the emitting classes — guards
     * against renames silently orphaning the legacy names.
     */
    public function test_legacy_hook_method_names_still_exist_on_emitters(): void
    {
        $serviceMethods = [
            'saveStatusLabels', 'quickAddTicket', 'quickAddMilestone', 'addTicket',
            'updateTicket', 'patch', 'quickUpdateMilestone', 'upsertSubtask',
            'updateTicketSorting', 'updateTicketStatusAndSorting', 'delete', 'deleteMilestone',
            'getTicketTemplateAssignments', 'getToDoWidgetAssignments', 'getToDoWidgetHierarchicalAssignments',
        ];

        foreach ($serviceMethods as $method) {
            $this->assertTrue(
                method_exists(\Leantime\Domain\Tickets\Services\Tickets::class, $method),
                "Tickets service method {$method} was renamed — its legacy event name is now orphaned"
            );
        }

        foreach (['patchTicket', 'updateTicketStatus'] as $method) {
            $this->assertTrue(
                method_exists(\Leantime\Domain\Tickets\Repositories\Tickets::class, $method),
                "Tickets repository method {$method} was renamed — its legacy event name is now orphaned"
            );
        }
    }
}
