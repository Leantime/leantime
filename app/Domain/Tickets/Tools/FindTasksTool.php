<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Support\TicketFormatter;

/**
 * Search for tasks across multiple projects efficiently.
 */
#[IsReadOnly]
class FindTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'findTasks';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Search for tasks across multiple projects efficiently. This is the primary tool for task discovery and should be used for ALL task searches, whether for single projects or multiple projects. Use this instead of separate project queries. Supports filtering by user, status, status type and last-modified time. Omit projectIds to search every project you can access (useful with modifiedAfter for "what changed since" checks). Important: Execute this tool only ONCE. Ensure you have all project ids you want to query ready and in this array.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('projectIds', ['type' => 'array', 'description' => 'Array of project IDs (numbers) to search. For multiple projects use [1,3,4,5]. This is more efficient than separate calls. Omit to search all accessible projects.'])
            ->string('modifiedAfter')->description('Only tasks modified (or created, if never modified) at or after this time. ISO8601 format (e.g. 2024-04-30T15:00:00-04:00).')
            ->string('modifiedBefore')->description('Only tasks modified at or before this time. ISO8601 format.')
            ->string('dateRangeFrom')->description('Alias of modifiedAfter.')
            ->string('dateRangeTo')->description('Alias of modifiedBefore.')
            ->integer('userId')->description('User ID to filter by. Empty for all users. 0 for current user.')
            ->string('status')->description('Status filter: open (not completed), done (completed), all (everything). Default is all.')
            ->string('statusType')->description('Status type filter, comma separated: NEW, INPROGRESS, DONE, NOT_DONE. Resolved per project, e.g. INPROGRESS for everything in progress.')
            ->integer('limit')->description('Maximum tasks per project (or in total when projectIds is omitted). Default 20.');
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectIds = is_array($arguments['projectIds'] ?? null) ? $arguments['projectIds'] : [];
        $userId = ($arguments['userId'] ?? null);
        $status = ($arguments['status'] ?? 'all');
        $limit = (int) ($arguments['limit'] ?? 20);

        $effectiveUserId = $userId;
        if ($effectiveUserId === null) {
            $effectiveUserId = '';
        }
        if ($effectiveUserId === 0) {
            $effectiveUserId = session('userdata.id') ?? '';
        }

        $baseCriteria = [
            'users' => $effectiveUserId,
            'modifiedAfter' => $arguments['modifiedAfter'] ?? $arguments['dateRangeFrom'] ?? '',
            'modifiedBefore' => $arguments['modifiedBefore'] ?? $arguments['dateRangeTo'] ?? '',
            'statusType' => $arguments['statusType'] ?? '',
        ];

        // status=open/done is resolved per project, so map it to the equivalent status type; that
        // also works when no project is given (the legacy not_done/done filter needs a project).
        if ($status === 'open' && $baseCriteria['statusType'] === '') {
            $baseCriteria['statusType'] = 'NOT_DONE';
        } elseif ($status === 'done' && $baseCriteria['statusType'] === '') {
            $baseCriteria['statusType'] = 'DONE';
        }

        // No projects: one search across every project the caller can access.
        $projectScopes = $projectIds === [] ? [null] : $projectIds;

        $allResults = [];
        $totalTasks = 0;

        foreach ($projectScopes as $projectId) {
            $searchCriteria = $baseCriteria;
            if ($projectId !== null) {
                $searchCriteria['currentProject'] = $projectId;
            }

            try {
                $tickets = $this->ticketsService->getAll($searchCriteria, $limit);
            } catch (LeantimeExceptionInterface $e) {
                return ToolResult::error($e->getClientMessage());
            }

            foreach ($tickets ?: [] as $ticket) {
                $allResults[$ticket['projectId']][] = $ticket;
                $totalTasks++;
            }
        }

        if (empty($allResults)) {
            return ToolResult::text('No tasks found for the specified criteria.');
        }

        $response = "## TASK RESULTS ACROSS PROJECTS\n";
        if ($totalTasks >= ($limit * count($projectScopes))) {
            $response .= "**Showing first {$limit} results per project. Use more specific filters to reduce results.**\n\n";
        }

        foreach ($allResults as $projectId => $tickets) {
            $projectName = $tickets[0]['projectName'] ?? "Project {$projectId}";
            $response .= "## {$projectName} (Project ID: {$projectId})\n";
            $response .= '**Found '.count($tickets)." tasks**\n\n";

            foreach ($tickets as $ticket) {
                $ticketModel = new TicketModel($ticket);
                $formatter = new TicketFormatter($ticketModel);
                $response .= $formatter->format()."\n\n";
            }

            $response .= "---\n\n";
        }

        return ToolResult::text($response);
    }
}
