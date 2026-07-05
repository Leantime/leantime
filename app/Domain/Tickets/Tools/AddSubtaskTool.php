<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create a subtask for an existing task.
 */
class AddSubtaskTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'addSubtask';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Creates a new subtask of another existing task. Tool to break down large tasks into smaller elements.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('parentTicket')->description('ID of the parent ticket.')->required()
            ->string('headline')->description('Title of the subtask.')->required()
            ->string('description')->description('Subtask description.')
            ->integer('projectId')->description('Project ID.')
            ->integer('editorId')->description('Assigned user ID.')
            ->integer('userId')->description('Creator user ID.')
            ->string('dateToFinish')->description('Due date in ISO8601 format.')
            ->integer('status')->description('Status ID.')
            ->string('editFrom')->description('Scheduled start date in ISO8601 format.')
            ->string('editTo')->description('Scheduled end date in ISO8601 format.')
            ->integer('effort')->description('Effort T-shirt size: 1=XS, 2=S, 3=M, 5=L, 8=XL, 13=XXL.')
            ->integer('planHours')->description('Planned hours for this subtask.')
            ->integer('priority')->description('Priority: 1=Critical, 2=High, 3=Medium, 4=Low, 5=Lowest.');
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $params = [
            'headline' => $arguments['headline'],
            'description' => ($arguments['description'] ?? ''),
            'projectId' => ($arguments['projectId'] ?? null),
            'editorId' => ($arguments['editorId'] ?? null),
            'userId' => ($arguments['userId'] ?? null),
            'dateToFinish' => ($arguments['dateToFinish'] ?? null),
            'status' => (int) ($arguments['status'] ?? 3),
            'sprint' => null,
            'editFrom' => ($arguments['editFrom'] ?? null),
            'editTo' => ($arguments['editTo'] ?? null),
            'milestone' => null,
            'type' => 'subtask',
            'dependingTicketId' => (int) ($arguments['parentTicket'] ?? 0),
            'storypoints' => (int) ($arguments['effort'] ?? 3),
            'priority' => (int) ($arguments['priority'] ?? 3),
            'planHours' => (int) ($arguments['planHours'] ?? 0),
        ];

        $result = $this->ticketsService->quickAddTicket($params);

        if ($result) {
            return ToolResult::text("Subtask created successfully. ID: {$result}");
        }

        return ToolResult::error('Failed to create subtask.');
    }
}
