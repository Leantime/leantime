<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create multiple milestones for a project in a single operation.
 */
class AddMilestonesForProjectTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'addMilestonesForProject';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Creates multiple milestones for a project in a single operation. This is the primary tool for project setup and should be used instead of multiple addMilestone calls. Each milestone should include headline, color, editFrom, editTo, and optionally dependentMilestone. Much more efficient than individual milestone creation for project planning phases.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID where milestones will be created.')->required()
            ->integer('editorId')->description('User ID who will be the editor/creator of these milestones.')->required()
            ->raw('milestones', ['type' => 'array', 'description' => 'Array of milestone definitions. Each element should contain: headline, color (hex), editFrom (ISO8601), editTo (ISO8601), and optionally dependentMilestone (ID). Example: [{"headline": "Phase 1", "color": "#FF0000", "editFrom": "2024-01-01T09:00:00Z", "editTo": "2024-01-31T17:00:00Z"}]'])->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $editorId = (int) ($arguments['editorId'] ?? 0);
        $milestones = ($arguments['milestones'] ?? []);

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

        return ToolResult::text(
            "Milestone creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
