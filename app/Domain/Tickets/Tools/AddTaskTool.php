<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create a new task.
 */
#[IsDestructive(false)]
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
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Add Task';
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
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'headline' => $schema->string()
                ->description('Title of the task.')
                ->required(),
            'description' => $schema->string()
                ->description('Task description.'),
            'projectId' => $schema->integer()
                ->description('Project ID.'),
            'editorId' => $schema->integer()
                ->description('Assigned user ID.'),
            'userId' => $schema->integer()
                ->description('Creator user ID.'),
            'dateToFinish' => $schema->string()
                ->description('Due date in ISO8601 format.'),
            'status' => $schema->union(['integer', 'string'])
                ->description('Status id (see getStatusLabels), status name ("New") or status type (new, inprogress, done). Defaults to the project\'s NEW status.'),
            'sprint' => $schema->integer()
                ->description('Sprint ID.'),
            'editFrom' => $schema->string()
                ->description('Scheduled start date in ISO8601 format.'),
            'editTo' => $schema->string()
                ->description('Scheduled end date in ISO8601 format.'),
            'milestone' => $schema->integer()
                ->description('Milestone ID.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

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
            $params['origin'] = 'mcp';
            $result = $this->ticketsService->quickAddTicket($params);
        } catch (LeantimeExceptionInterface $e) {
            return Response::error($e->getClientMessage());
        }

        if (is_array($result)) {
            return Response::error((string) ($result['message'] ?? $result['msg'] ?? 'Failed to create the task.'));
        }

        if ($result) {
            return Response::text("Task created successfully. ID: {$result}");
        }

        return Response::error('Failed to create task.');
    }
}
