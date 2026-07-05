<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Support\TicketFormatter;

/**
 * Get a single task by ID.
 */
#[IsReadOnly]
class GetTaskTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'getTicket';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Gets one individual task by task id. If the user is not allowed to see the task, false is returned. All dates are returned in the format YYYY-MM-DD hh:mm:ss in the UTC timezone';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the task to retrieve.')->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        $ticket = $this->ticketsService->getTicket($id);

        if ($ticket) {
            $formatter = new TicketFormatter($ticket);

            return ToolResult::text($formatter->format());
        }

        return ToolResult::error("Could not find task with id {$id}");
    }
}
