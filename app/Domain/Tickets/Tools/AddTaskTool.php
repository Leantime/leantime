<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create a new task.
 */
class AddTaskTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'addTask';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Adds a new task quickly based on the provided parameters.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('headline')->description('Title of the task.')->required()
            ->string('description')->description('Task description.')
            ->integer('projectId')->description('Project ID.')
            ->integer('editorId')->description('Assigned user ID.')
            ->integer('userId')->description('Creator user ID.')
            ->string('dateToFinish')->description('Due date in ISO8601 format.')
            ->raw('status', ['type' => ['integer', 'string'], 'description' => 'Status id (see getStatusLabels), status name ("New") or status type (new, inprogress, done). Defaults to the project\'s NEW status.'])
            ->integer('sprint')->description('Sprint ID.')
            ->string('editFrom')->description('Scheduled start date in ISO8601 format.')
            ->string('editTo')->description('Scheduled end date in ISO8601 format.')
            ->integer('milestone')->description('Milestone ID.');
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
            'status' => ($arguments['status'] ?? null),
            'sprint' => ($arguments['sprint'] ?? null),
            'editFrom' => ($arguments['editFrom'] ?? null),
            'editTo' => ($arguments['editTo'] ?? null),
            'milestone' => ($arguments['milestone'] ?? null),
            'type' => 'task',
        ];

        try {
            $result = $this->ticketsService->quickAddTicket($params);
        } catch (LeantimeExceptionInterface $e) {
            return ToolResult::error($e->getClientMessage());
        }

        if (is_array($result)) {
            return ToolResult::error((string) ($result['message'] ?? $result['msg'] ?? 'Failed to create the task.'));
        }

        if ($result) {
            return ToolResult::text("Task created successfully. ID: {$result}");
        }

        return ToolResult::error('Failed to create task.');
    }
}
