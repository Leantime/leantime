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
 * Poll for all comments across the account.
 */
#[IsReadOnly]
class PollCommentsTool extends Tool
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
                ->description('Project ID to filter comments by.'),
            'moduleId' => $schema->integer()
                ->description('Module ID to filter comments by.'),
        ];
    }

    public function name(): string
    {
        return 'pollComments';
    }

    public function title(): string
    {
        return 'Poll Comments';
    }

    public function description(): string
    {
        return 'Polls for all comments across the account.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = ($arguments['projectId'] ?? null);
        $moduleId = ($arguments['moduleId'] ?? null);

        $comments = $this->commentsService->pollComments($projectId, $moduleId);

        if (empty($comments)) {
            return Response::text('No comments found');
        }

        $response = "## Comments\n";
        foreach ($comments as $comment) {
            $statusIndicator = '';
            if (isset($comment['status'])) {
                $statusIndicator = match ($comment['status']) {
                    'green' => '🟢 ',
                    'yellow' => '🟡 ',
                    'red' => '🔴 ',
                    default => '',
                };
            }

            $result = [
                'id' => $comment['id'],
                'module' => $comment['module'],
                'moduleId' => $comment['moduleId'],
                'status' => $comment['status'] ? $statusIndicator.$comment['status'] : 'None',
                'text' => Str::sanitizeForLLM($comment['text']),
                'date' => $comment['date'],
                'projectId' => $comment['projectId'],
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return Response::text($response);
    }
}
