<?php

namespace Leantime\Domain\Comments\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Comments\Services\Comments;

/**
 * Add a new comment to a specific entity.
 */
#[IsDestructive(false)]
class AddCommentTool extends Tool
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
            'text' => $schema->string()
                ->description('Comment text.')
                ->required(),
            'module' => $schema->string()
                ->description('Module type: ticket, project, article (wiki), idea, goal or {type}canvasitem.')
                ->required(),
            'entityId' => $schema->integer()
                ->description('ID of the entity to add comment to.')
                ->required(),
            'status' => $schema->string()
                ->description('Status indicator for project updates (green, yellow, red). Only used for project comments.'),
        ];
    }

    public function name(): string
    {
        return 'addComment';
    }

    public function title(): string
    {
        return 'Add Comment';
    }

    public function description(): string
    {
        return 'Adds a new comment to a specific entity.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $module = (string) ($arguments['module'] ?? '');
        $entityId = (int) ($arguments['entityId'] ?? 0);

        if (trim((string) ($arguments['text'] ?? '')) === '') {
            return Response::error('The comment text is empty.');
        }

        $values = [
            'text' => $arguments['text'],
            'father' => 0,
            'status' => ($arguments['status'] ?? ''),
        ];

        // The service resolves (and access-checks) the entity from module + id, normalizes module
        // aliases such as "tickets"/"goal", and reports unknown modules or missing entities as
        // errors. A placeholder entity used to be passed for unknown modules, which stored
        // comments under modules nothing reads (#3704).
        try {
            $result = $this->commentsService->addComment($values, $module, $entityId);
        } catch (LeantimeExceptionInterface $e) {
            return Response::error($e->getClientMessage());
        }

        if ($result) {
            return Response::text("Comment added successfully to {$module} #{$entityId}");
        }

        return Response::error('Failed to add comment. Please check the provided information.');
    }
}
