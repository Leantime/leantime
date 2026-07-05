<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
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
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Gets one individual milestone by milestone id. If the user is not allowed to see the task, false is returned. All dates are returned in the format YYYY-MM-DD hh:mm:ss in the UTC timezone';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the milestone to retrieve.')->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        $ticket = $this->ticketsService->getTicket($id);

        if (! $ticket) {
            return ToolResult::error("Milestone with ID {$id} not found.");
        }

        return ToolResult::text(Str::toMarkdown($ticket)."\n");
    }
}
