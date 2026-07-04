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
 * Get all comments for a specific entity.
 */
#[Name('getComments')]
#[Description('Gets all comments for a specific entity (ticket, project, goal, etc.). When comments are attached to a project, they serve as status updates with red/yellow/green indicators.')]
#[IsReadOnly]
class GetCommentsTool extends Tool
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
            'module' => $schema->string()
                ->description('Module type (ticket, project, goal, etc.).')
                ->required(),
            'entityId' => $schema->integer()
                ->description('ID of the entity to get comments for.')
                ->required(),
            'commentOrder' => $schema->integer()
                ->description('Order of comments (0 = newest first, 1 = oldest first).')
                ->default(0),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $module = $request->string('module');
        $entityId = $request->integer('entityId');
        $commentOrder = $request->integer('commentOrder', 0);

        $comments = $this->commentsService->getComments($module, $entityId, $commentOrder);

        if (empty($comments)) {
            return Response::text("No comments found for {$module} ID: {$entityId}");
        }

        $response = "## Comments for {$module} #{$entityId}\n";
        foreach ($comments as $comment) {
            $result = [
                'id' => $comment['id'],
                'text' => Str::sanitizeForLLM($comment['text']),
                'date' => $comment['date'],
                'userId' => $comment['userId'],
                'author' => $comment['firstname'].' '.$comment['lastname'],
                'status' => $comment['status'] ?: 'None',
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return Response::text($response);
    }
}
