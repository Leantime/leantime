<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets all projects assigned to a specific user.
 */
#[IsReadOnly]
class GetProjectsAssignedToUserTool extends Tool
{
    public function __construct(
        private Projects $projectService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'userId' => $schema->integer()
                ->description('User ID to get projects for.')
                ->required(),
            'projectStatus' => $schema->string()
                ->description('Filter by project status (open, closed, all).'),
            'clientId' => $schema->integer()
                ->description('Filter by client ID.'),
            'projectTypes' => $schema->string()
                ->description('Filter by project types (comma-separated: project, program, etc.). Use "all" for all project types.'),
        ];
    }

    public function name(): string
    {
        return 'getProjectsAssignedToUser';
    }

    public function title(): string
    {
        return 'Get Projects Assigned To User';
    }

    public function description(): string
    {
        return 'Gets all projects assigned to a specific user.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $userId = (int) ($arguments['userId'] ?? 0);
        $projectStatus = ($arguments['projectStatus'] ?? 'open');
        $clientId = ($arguments['clientId'] ?? null);
        $projectTypes = ($arguments['projectTypes'] ?? 'all');

        if ($userId === 0) {
            $userId = session('userdata.id') ?? '';
        }

        $projects = $this->projectService->getProjectsAssignedToUser($userId, $projectStatus, $clientId, $projectTypes);

        $response = "## Projects Assigned to User\n";
        foreach ($projects as $project) {
            $result = [
                'id' => $project['id'],
                'name' => Str::sanitizeForLLM($project['name']),
                'clientName' => Str::sanitizeForLLM($project['clientName']),
                'type' => $project['type'],
                'state' => $project['state'],
                'start' => $project['start'] ?? 'Not set',
                'end' => $project['end'] ?? 'Not set',
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        if (empty($projects)) {
            return Response::text('No projects found.');
        }

        return Response::text($response);
    }
}
