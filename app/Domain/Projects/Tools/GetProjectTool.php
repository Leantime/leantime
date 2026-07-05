<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets detailed information about a specific project by its ID.
 */
#[IsReadOnly]
class GetProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Comments $commentsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('ID of the project to retrieve.')
            ->required();
    }

    public function name(): string
    {
        return 'getProject';
    }

    public function description(): string
    {
        return 'Gets detailed information about a specific project by its ID and project progress.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $project = $this->projectService->getProject($projectId);

        if (! $project) {
            return ToolResult::error('Project not found.');
        }

        $progress = $this->projectService->getProjectProgress($projectId);
        $projectComment = $this->commentsService->getComments('project', $project['id']);
        $project['team'] = $this->projectService->getUsersAssignedToProject($project['id']);

        if (is_array($projectComment) && count($projectComment) > 0) {
            $project['lastUpdate'] = $projectComment[0];
            $project['status'] = $projectComment[0]['status'];
        } else {
            $project['lastUpdate'] = false;
            $project['status'] = '';
        }

        $project['progress'] = $progress;

        $response = "## Project Details\n";
        $result = [
            'id' => $project['id'],
            'name' => Str::sanitizeForLLM($project['name']),
            'details' => Str::sanitizeForLLM($project['details']),
            'clientName' => Str::sanitizeForLLM($project['clientName'] ?? ''),
            'type' => $project['type'],
            'state' => $project['state'],
            'ragStatus' => $project['status'],
            'lastUpdateMessage' => $project['lastUpdate'],
            'start' => $project['start'] ?? 'Not set',
            'end' => $project['end'] ?? 'Not set',
            'progress' => isset($progress['percent']) ? round($progress['percent']).'%' : 'Not calculated',
            'estimatedCompletionDate' => isset($progress['estimatedCompletionDate']) ? strip_tags($progress['estimatedCompletionDate']) : 'Unknown',
        ];
        $response .= Str::toMarkdown($result)."\n";

        return ToolResult::text($response);
    }
}
