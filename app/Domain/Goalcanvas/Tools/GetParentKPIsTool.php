<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Get all available parent KPIs (goals) that can be linked to other goals.
 */
#[Name('getParentKPIs')]
#[Description('Gets all available parent KPIs (goals) that can be linked to other goals.')]
#[IsReadOnly]
class GetParentKPIsTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Project ID to get parent KPIs for.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $parentKPIs = $this->goalcanvasService->getParentKPIs($projectId);

        if (empty($parentKPIs)) {
            return Response::text("No parent KPIs found for project ID: {$projectId}");
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

        return Response::text($response);
    }
}
