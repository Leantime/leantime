<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Get all available parent KPIs (goals) that can be linked to other goals.
 */
#[IsReadOnly]
class GetParentKPIsTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
        private Projects $projectService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID to get parent KPIs for.')
            ->required();
    }

    public function name(): string
    {
        return 'getParentKPIs';
    }

    public function description(): string
    {
        return 'Gets all available parent KPIs that can be linked to other goals.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);

        // Only for a project the caller can view: getProject() returns false otherwise, so a
        // foreign project id must not expose its KPIs.
        if ($projectId <= 0 || ! $this->projectService->getProject($projectId)) {
            return ToolResult::error("Project {$projectId} was not found or you do not have access to it.");
        }

        $parentKPIs = $this->goalcanvasService->getParentKPIs($projectId);

        if (empty($parentKPIs)) {
            return ToolResult::text("No parent KPIs found for project ID: {$projectId}");
        }

        $response = "## Available Parent KPIs for Project ID: {$projectId}\n";
        foreach ($parentKPIs as $kpi) {
            $result = [
                'id' => $kpi['id'],
                'description' => Str::sanitizeForLLM($kpi['description']),
                'project' => Str::sanitizeForLLM($kpi['project']),
                'board' => Str::sanitizeForLLM($kpi['board']),
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
