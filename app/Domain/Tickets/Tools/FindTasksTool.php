<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Support\TicketFormatter;

/**
 * Search for tasks across multiple projects efficiently.
 */
#[Name('findTasks')]
#[Description('Search for tasks across multiple projects. Primary tool for task discovery. Supports filtering by user, status, and date ranges. Execute once with all project IDs.')]
#[IsReadOnly]
class FindTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectIds' => $schema->array()
                ->description('Array of project IDs to search. For multiple projects use [1,3,4,5].')
                ->required(),
            'dateRangeFrom' => $schema->string()
                ->description('Modified date range from filter. ISO8601 format (e.g. 2024-04-30T15:00:00-04:00).'),
            'dateRangeTo' => $schema->string()
                ->description('Modified date range to filter. ISO8601 format.'),
            'userId' => $schema->integer()
                ->description('User ID to filter by. Empty for all users. 0 for current user.'),
            'status' => $schema->string()
                ->enum(['open', 'done', 'all'])
                ->description('Status filter: open (not completed), done (completed), all (everything).')
                ->default('all'),
            'limit' => $schema->integer()
                ->description('Maximum tasks per project. Default 20.')
                ->default(20),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectIds = $request->array('projectIds');
        $userId = $request->get('userId');
        $status = $request->string('status', 'all');
        $limit = $request->integer('limit', 20);

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

            if ($tickets && count($tickets) > 0) {
                $allResults[$projectId] = $tickets;
                $totalTasks += count($tickets);
            }
        }

        if (empty($allResults)) {
            return Response::text('No tasks found for the specified criteria.');
        }

        $response = "## TASK RESULTS ACROSS PROJECTS\n";
        if ($totalTasks >= ($limit * count($projectIds))) {
            $response .= "**Showing first {$limit} results per project. Use more specific filters to reduce results.**\n\n";
        }

        foreach ($allResults as $projectId => $tickets) {
            $projectName = $tickets[0]['projectName'] ?? "Project {$projectId}";
            $response .= "## 📁 {$projectName} (Project ID: {$projectId})\n";
            $response .= '**Found '.count($tickets)." tasks**\n\n";

            foreach ($tickets as $ticket) {
                $ticketModel = new TicketModel($ticket);
                $formatter = new TicketFormatter($ticketModel);
                $response .= $formatter->format()."\n\n";
            }

            $response .= "---\n\n";
        }

        return Response::text($response);
    }
}
