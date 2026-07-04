<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Support\TicketFormatter;

/**
 * Get a single task by ID.
 */
#[Name('getTicket')]
#[Description('Gets one individual task by task ID. Returns false if user lacks access. All dates in UTC format YYYY-MM-DD hh:mm:ss.')]
#[IsReadOnly]
class GetTaskTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('ID of the task to retrieve.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->integer('id');
        $ticket = $this->ticketsService->getTicket($id);

        if ($ticket) {
            $formatter = new TicketFormatter($ticket);

            return Response::text($formatter->format());
        }

        return Response::error("Could not find task with id {$id}");
    }
}
