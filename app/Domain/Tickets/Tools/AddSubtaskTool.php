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
 * Create a subtask for an existing task.
 */
#[Name('addSubtask')]
#[Description('Creates a new subtask of an existing task. Use to break down large tasks into smaller elements.')]
class AddSubtaskTool extends Tool
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
            'parentTicket' => $schema->string()
                ->description('ID of the parent ticket.')
                ->required(),
            'headline' => $schema->string()
                ->description('Title of the subtask.')
                ->required(),
            'description' => $schema->string()
                ->description('Subtask description.'),
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
            'editFrom' => $schema->string()
                ->description('Scheduled start date in ISO8601 format.'),
            'editTo' => $schema->string()
                ->description('Scheduled end date in ISO8601 format.'),
            'effort' => $schema->integer()
                ->description('Effort T-shirt size: 1=XS, 2=S, 3=M, 5=L, 8=XL, 13=XXL.'),
            'planHours' => $schema->integer()
                ->description('Planned hours for this subtask.'),
            'priority' => $schema->integer()
                ->description('Priority: 1=Critical, 2=High, 3=Medium, 4=Low, 5=Lowest.'),
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
            'sprint' => null,
            'editFrom' => $request->get('editFrom'),
            'editTo' => $request->get('editTo'),
            'milestone' => null,
            'type' => 'subtask',
            'dependingTicketId' => $request->string('parentTicket'),
            'storypoints' => $request->integer('effort', 3),
            'priority' => $request->integer('priority', 3),
            'planHours' => $request->integer('planHours', 0),
        ];

        $result = $this->ticketsService->quickAddTicket($params);

        if ($result) {
            return Response::text("Subtask created successfully. ID: {$result}");
        }

        return Response::error('Failed to create subtask.');
    }
}
