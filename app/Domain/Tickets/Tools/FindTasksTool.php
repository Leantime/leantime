<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
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
        return 'Search for tasks across multiple projects efficiently. This is the primary tool for task discovery and should be used for ALL task searches, whether for single projects or multiple projects. Use this instead of separate project queries. Supports filtering by user, status, and date ranges. Important: Execute this tool only ONCE. Ensure you have all project ids you want to query ready and in this array.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('projectIds', ['type' => 'array', 'description' => 'Array of project IDs (numbers) to search. For multiple projects use [1,3,4,5]. This is more efficient than separate calls.'])->required()
            ->string('dateRangeFrom')->description('Modified date range from filter. ISO8601 format (e.g. 2024-04-30T15:00:00-04:00).')
            ->string('dateRangeTo')->description('Modified date range to filter. ISO8601 format.')
            ->integer('userId')->description('User ID to filter by. Empty for all users. 0 for current user.')
            ->string('status')->description('Status filter: open (not completed), done (completed), all (everything). Default is all.')
            ->integer('limit')->description('Maximum tasks per project. Default 20.');
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectIds = ($arguments['projectIds'] ?? []);
        $userId = ($arguments['userId'] ?? null);
        $status = ($arguments['status'] ?? 'all');
        $limit = (int) ($arguments['limit'] ?? 20);

        $allResults = [];
        $totalTasks = 0;

        foreach ($projectIds as $projectId) {
            $effectiveUserId = $userId;
            if ($effectiveUserId === null) {
                $effectiveUserId = '';
            }
            if ($effectiveUserId === 0) {
                $effectiveUserId = session('userdata.id') ?? '';
            }

            $searchCriteria = [
                'users' => $effectiveUserId,
                'currentProject' => $projectId,
            ];

            if ($status === 'open') {
                $searchCriteria['status'] = 'not_done';
            } elseif ($status === 'done') {
                $searchCriteria['status'] = 'done';
            }

            $tickets = $this->ticketsService->getAll($searchCriteria, $limit);

            if (! empty($tickets)) {
                $allResults[$projectId] = $tickets;
                $totalTasks += count($tickets);
            }
        }

        if (empty($allResults)) {
            return ToolResult::text('No tasks found for the specified criteria.');
        }

        $response = "## TASK RESULTS ACROSS PROJECTS\n";
        if ($totalTasks >= ($limit * count($projectIds))) {
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
