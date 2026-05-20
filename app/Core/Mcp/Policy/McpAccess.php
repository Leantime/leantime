<?php

namespace Leantime\Core\Mcp\Policy;

use Leantime\Core\Mcp\Auth\McpPrincipal;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

class McpAccess
{
    public function __construct(
        private ProjectRepository $projectsRepository,
        private TicketRepository $ticketsRepository,
    ) {}

    public function assertAbility(McpPrincipal $principal, array $abilities): void
    {
        foreach ($abilities as $ability) {
            if (! $principal->can($ability)) {
                throw new McpException("Missing required ability: {$ability}", -32003, 403);
            }
        }
    }

    public function canManageAllProjects(McpPrincipal $principal): bool
    {
        return in_array($principal->role, ['manager', 'admin', 'owner'], true);
    }

    public function assertProjectAccess(McpPrincipal $principal, int $projectId): array
    {
        $project = $this->projectsRepository->getProject($projectId);
        if ($project === false) {
            throw new McpException('Project not found', -32004, 404);
        }

        if ($this->canManageAllProjects($principal)) {
            return $project;
        }

        if (! $this->projectsRepository->isUserAssignedToProject($principal->userId, $projectId)) {
            throw new McpException('Project access denied', -32003, 403);
        }

        return $project;
    }

    public function assertTicketAccess(McpPrincipal $principal, int $ticketId): TicketModel
    {
        $ticket = $this->ticketsRepository->getTicket($ticketId);
        if ($ticket === false) {
            throw new McpException('Ticket not found', -32004, 404);
        }

        $this->assertProjectAccess($principal, (int) $ticket->projectId);

        return $ticket;
    }
}
