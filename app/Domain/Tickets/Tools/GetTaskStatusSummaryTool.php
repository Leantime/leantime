<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Compact status counts plus the list of active tasks, for watcher/checkpoint agents (#3703).
 */
#[IsReadOnly]
class GetTaskStatusSummaryTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'getTaskStatusSummary';
    }

    /**
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Get Task Status Summary';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Compact task discovery: returns JSON with status counts (by status type and by project status) and a slim list of active tasks (id, headline, project, status, assignee editorId, parent dependingTicketId, modified, commentCount), most recently modified first. Use this for periodic check-ins instead of pulling every task with findTasks.';
    }

    /**
     * Define the tool input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Limit to one project. Omit for every project you can access.'),
            'statusType' => $schema->string()
                ->description('Which tasks to list as active (comma separated): NEW, INPROGRESS, DONE, NOT_DONE. Default INPROGRESS.'),
            'modifiedAfter' => $schema->string()
                ->description('Only count and list tasks changed at or after this time. ISO8601 format.'),
            'includeSubtasks' => $schema->boolean()
                ->description('Include subtasks. Default true.'),
            'limit' => $schema->integer()
                ->description('Maximum active tasks to return (1-500). Default 50.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = isset($arguments['projectId']) && (int) $arguments['projectId'] > 0 ? (int) $arguments['projectId'] : null;

        try {
            $summary = $this->ticketsService->getStatusSummary(
                projectId: $projectId,
                statusType: $arguments['statusType'] ?? 'INPROGRESS',
                modifiedAfter: $arguments['modifiedAfter'] ?? null,
                includeSubtasks: (bool) ($arguments['includeSubtasks'] ?? true),
                activeLimit: (int) ($arguments['limit'] ?? 50),
            );
        } catch (LeantimeExceptionInterface $e) {
            return Response::error($e->getClientMessage());
        }

        return Response::text((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
