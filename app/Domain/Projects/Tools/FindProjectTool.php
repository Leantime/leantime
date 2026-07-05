<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Searches for projects by name.
 */
#[IsReadOnly]
class FindProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('term')->description('Search term to find in project names.')
            ->required();
    }

    public function name(): string
    {
        return 'findProject';
    }

    public function description(): string
    {
        return 'Searches for projects by name.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $term = $arguments['term'];
        $projects = $this->projectService->findProject($term);

        if (empty($projects)) {
            return ToolResult::text("No projects found matching: '$term'.");
        }

        $response = "## Projects Matching: '$term'\n";
        foreach ($projects as $project) {
            $result = [
                'id' => $project['id'],
                'name' => Str::sanitizeForLLM($project['name']),
                'clientName' => Str::sanitizeForLLM($project['clientName']),
                'type' => $project['type'],
                'state' => $project['state'],
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
