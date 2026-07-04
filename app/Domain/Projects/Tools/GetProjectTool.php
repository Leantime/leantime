<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets detailed information about a specific project by its ID.
 */
#[Name('getProject')]
#[Description('Gets detailed information about a specific project by its ID and project progress. Will return a red, yellow, green indicator if one is present including the last update message.')]
#[IsReadOnly]
class GetProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Comments $commentsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('ID of the project to retrieve.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $project = $this->projectService->getProject($projectId);

        if (! $project) {
            return Response::error('Project not found.');
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

        return Response::text($response);
    }
}
