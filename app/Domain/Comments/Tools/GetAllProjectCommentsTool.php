<?php

namespace Leantime\Domain\Comments\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Comments\Services\Comments;

/**
 * Get all project status updates (comments) for a specific project.
 */
#[Name('getAllProjectComments')]
#[Description('Gets all project status updates (comments) for a specific project. These are the red/yellow/green status indicators shown on the dashboard.')]
#[IsReadOnly]
class GetAllProjectCommentsTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Project ID to get status updates for.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $comments = $this->commentsService->getComments('project', $projectId);

        if (empty($comments)) {
            return Response::text("No status updates found for project ID: {$projectId}");
        }

        $response = "## Project Status Updates\n";
        foreach ($comments as $comment) {
            $statusIndicator = match ($comment['status']) {
                'green' => '🟢 ',
                'yellow' => '🟡 ',
                'red' => '🔴 ',
                default => '',
            };

            $result = [
                'id' => $comment['id'],
                'status' => $statusIndicator.($comment['status'] ?: 'None'),
                'text' => Str::sanitizeForLLM($comment['text']),
                'date' => $comment['date'],
                'author' => $comment['firstname'].' '.$comment['lastname'],
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return Response::text($response);
    }
}
