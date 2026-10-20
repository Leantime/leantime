<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Get a single milestone by ID.
 */
#[IsReadOnly]
class GetMilestoneTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'getMilestone';
    }

    /**
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Get Milestone';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Gets one individual milestone by milestone id. If the user is not allowed to see the task, false is returned. All dates are returned in the format YYYY-MM-DD hh:mm:ss in the UTC timezone';
    }

    /**
     * Define the tool input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('ID of the milestone to retrieve.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $id = (int) ($arguments['id'] ?? 0);
        $ticket = $this->ticketsService->getTicket($id);

        if (! $ticket) {
            return Response::error("Milestone with ID {$id} not found.");
        }

        return Response::text(Str::toMarkdown($ticket)."\n");
    }
}
