<?php

namespace Leantime\Domain\Reminders\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Reminders\Services\Reminders;

/**
 * What is coming up for the signed-in person: the same list their morning summary sends.
 */
#[IsReadOnly]
class GetComingUpTool extends Tool
{
    use ToolErrors;

    public function __construct(
        private Reminders $reminders,
    ) {}

    public function name(): string
    {
        return 'getComingUp';
    }

    public function title(): string
    {
        return 'Get Coming Up';
    }

    public function description(): string
    {
        return 'Gets what is coming up for the signed-in user: their to-dos due today and tomorrow, milestones and projects ending soon, routine to-dos for today and how many older to-dos are still open. It is the same list their morning summary sends, so it follows their reminder settings. daysUntil is 0 for today. Dates are UTC.';
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Response
    {
        try {
            return Response::json($this->reminders->getSummary());
        } catch (LeantimeExceptionInterface $e) {
            return self::errorResponse($e);
        }
    }
}
