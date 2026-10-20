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
 * Create a subtask for an existing task.
 */
#[IsDestructive(false)]
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
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Add Subtask';
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
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'parentTicket' => $schema->integer()
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
            'status' => $schema->union(['integer', 'string'])
                ->description('Status id (see getStatusLabels), status name ("New") or status type (new, inprogress, done). Defaults to the project\'s NEW status.'),
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
        $arguments = $request->all();

        $params = [
            'headline' => $arguments['headline'],
            'description' => ($arguments['description'] ?? ''),
            'projectId' => ($arguments['projectId'] ?? null),
            'editorId' => ($arguments['editorId'] ?? null),
            'userId' => ($arguments['userId'] ?? null),
            'dateToFinish' => ($arguments['dateToFinish'] ?? null),
            'status' => ($arguments['status'] ?? null),
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
            return Response::text("Subtask created successfully. ID: {$result}");
        }

        return Response::error('Failed to create subtask.');
    }
}
