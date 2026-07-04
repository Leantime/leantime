<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create multiple milestones for a project in a single operation.
 */
#[Name('addMilestonesForProject')]
#[Description('Creates multiple milestones for a project in a single operation. This is the primary tool for project setup and should be used instead of multiple addMilestone calls. Each milestone should include headline, color, editFrom, editTo, and optionally dependentMilestone. Much more efficient than individual milestone creation for project planning phases.')]
class AddMilestonesForProjectTool extends Tool
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
            'projectId' => $schema->integer()
                ->description('Project ID where milestones will be created.')
                ->required(),
            'editorId' => $schema->integer()
                ->description('User ID who will be the editor/creator of these milestones.')
                ->required(),
            'milestones' => $schema->array()
                ->description('Array of milestone definitions. Each element should contain: headline, color (hex), editFrom (ISO8601), editTo (ISO8601), and optionally dependentMilestone (ID). Example: [{"headline": "Phase 1", "color": "#FF0000", "editFrom": "2024-01-01T09:00:00Z", "editTo": "2024-01-31T17:00:00Z"}]')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $editorId = $request->integer('editorId');
        $milestones = $request->array('milestones');

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($milestones as $milestoneData) {
            try {
                if (! isset($milestoneData['headline']) || ! isset($milestoneData['color']) ||
                    ! isset($milestoneData['editFrom']) || ! isset($milestoneData['editTo'])) {
                    $failureCount++;
                    $results[] = [
                        'headline' => $milestoneData['headline'] ?? 'Unknown',
                        'status' => 'error',
                        'message' => 'Missing required fields (headline, color, editFrom, editTo)',
                    ];

                    continue;
                }

                $params = [
                    'headline' => $milestoneData['headline'],
                    'projectId' => $projectId,
                    'editorId' => $editorId,
                    'dependentMilestone' => $milestoneData['dependentMilestone'] ?? null,
                    'tags' => $milestoneData['color'],
                    'editFrom' => $milestoneData['editFrom'],
                    'editTo' => $milestoneData['editTo'],
                ];

                $result = $this->ticketsService->quickAddMilestone($params);

                if ($result) {
                    $successCount++;
                    $results[] = [
                        'headline' => $milestoneData['headline'],
                        'status' => 'success',
                        'id' => $result,
                    ];
                } else {
                    $failureCount++;
                    $results[] = [
                        'headline' => $milestoneData['headline'],
                        'status' => 'error',
                        'message' => 'Failed to create milestone',
                    ];
                }
            } catch (\Exception $e) {
                $failureCount++;
                $results[] = [
                    'headline' => $milestoneData['headline'] ?? 'Unknown',
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return Response::text(
            "Milestone creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
