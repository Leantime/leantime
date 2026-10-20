<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Update multiple tasks in a single operation.
 */
#[IsIdempotent]
class BulkEditTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'bulkEditTasks';
    }

    /**
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Bulk Edit Tasks';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Updates multiple tasks in a single operation. Expects an array where each element contains ticketId and the fields to update. Field names that can be updated are: headline, description, projectId, editorId, userId, dateToFinish, status, editFrom, editTo, milestoneId';
    }

    /**
     * Define the tool input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'updates' => $schema->array()->items($schema->object())
                ->description('Array of update objects. Each must have id and the fields to update.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $updates = ($arguments['updates'] ?? []);
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($updates as $update) {
            if (! isset($update['id'])) {
                $failureCount++;
                $results[] = ['status' => 'error', 'message' => 'Missing task ID'];

                continue;
            }

            $id = $update['id'];
            unset($update['id']);

            try {
                $ignoredFields = $this->ticketsService->getIgnoredPatchFields($update);
                $updated = $this->ticketsService->patchTicket((int) $id, $update);
            } catch (LeantimeExceptionInterface $e) {
                $failureCount++;
                $results[] = ['id' => $id, 'status' => 'error', 'message' => $e->getClientMessage()];

                continue;
            }

            if ($updated) {
                $successCount++;
                $result = ['id' => $id, 'status' => 'success'];
                if ($ignoredFields !== []) {
                    $result['message'] = 'Ignored unknown fields: '.implode(', ', $ignoredFields);
                }
                $results[] = $result;
            } else {
                $failureCount++;
                $results[] = ['id' => $id, 'status' => 'error', 'message' => 'Failed to update task'];
            }
        }

        return Response::text(
            "Bulk task update completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
