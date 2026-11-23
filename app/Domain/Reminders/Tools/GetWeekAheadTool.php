<?php

namespace Leantime\Domain\Reminders\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Reminders\Services\SummaryBuilder;

/**
 * The signed-in person's week: the same sections their weekly summary sends.
 */
#[IsReadOnly]
class GetWeekAheadTool extends Tool
{
    use ToolErrors;

    public function __construct(
        private Reminders $reminders,
    ) {}

    public function name(): string
    {
        return 'getWeekAhead';
    }

    public function title(): string
    {
        return 'Get Week Ahead';
    }

    public function description(): string
    {
        return 'Gets the signed-in user\'s week, the same sections their weekly summary sends. week.start/end are the 7 local days from today. ahead: to-dos due this week (theirs, plus others they opted in to; daysUntil 0 is today), milestones and projects ending this week, planned days (to-dos with a Work Start on each day, timeBoxed = with a set time), routine and "still open from earlier" counts. lookBack: to-dos they finished in the 7 days before today (count, up to 3 named, with finishedAt and closedBy), or null when nothing or when they turned it off. sections lists every section in order with a stable kind (today, ahead, lookBack, plugin:{plugin}.{section}). Pass includeToday true to add today\'s morning summary. Dates are UTC.';
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'includeToday' => $schema->boolean()
                ->description('Add today\'s morning summary as the "today" section. Default false.'),
            'limit' => $schema->integer()
                ->description('How many items "ahead" lists. Leave it out for 5 (moreCount says how many more there are), or 0 for all of them.'),
        ];
    }

    public function handle(Request $request): Response
    {
        try {
            $limit = $request->get('limit');

            return Response::json($this->reminders->getWeeklySummary(
                (bool) $request->get('includeToday', false),
                is_numeric($limit) ? (int) $limit : SummaryBuilder::MAX_ITEMS,
            ));
        } catch (LeantimeExceptionInterface $e) {
            return self::errorResponse($e);
        }
    }
}
