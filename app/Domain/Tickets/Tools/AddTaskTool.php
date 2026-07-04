<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create a new task.
 */
#[Name('addTask')]
#[Description('Creates a new task with the provided parameters.')]
class AddTaskTool extends Tool
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
            'status' => $schema->integer()
                ->description('Status ID.'),
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
        $params = [
            'headline' => $request->string('headline'),
            'description' => $request->string('description', ''),
            'projectId' => $request->get('projectId'),
            'editorId' => $request->get('editorId'),
            'userId' => $request->get('userId'),
            'dateToFinish' => $request->get('dateToFinish'),
            'status' => $request->integer('status', 3),
            'sprint' => $request->get('sprint'),
            'editFrom' => $request->get('editFrom'),
            'editTo' => $request->get('editTo'),
            'milestone' => $request->get('milestone'),
            'type' => 'task',
        ];

        $result = $this->ticketsService->quickAddTicket($params);

        if ($result) {
            return Response::text("Task created successfully. ID: {$result}");
        }

        return Response::error('Failed to create task.');
    }
}
