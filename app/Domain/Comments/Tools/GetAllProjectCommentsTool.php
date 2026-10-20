<?php

namespace Leantime\Domain\Comments\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Comments\Services\Comments;

/**
 * Get all project status updates (comments) for a specific project.
 */
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

    public function name(): string
    {
        return 'getAllProjectComments';
    }

    public function title(): string
    {
        return 'Get All Project Comments';
    }

    public function description(): string
    {
        return 'Gets all project status updates for a specific project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = (int) ($arguments['projectId'] ?? 0);
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
