<?php

namespace Leantime\Domain\Timesheets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get a weekly view of timesheet entries.
 */
#[IsReadOnly]
class GetWeeklyTimesheetsTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('weekStart')->description('Start date of the week in ISO8601 format.')
            ->required()
            ->integer('projectId')->description('Project ID to filter by (optional).')
            ->integer('userId')->description('User ID to filter by (optional, managers only).');
    }

    public function name(): string
    {
        return 'getWeeklyTimesheets';
    }

    public function description(): string
    {
        return 'Gets a weekly view of timesheet entries.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $currentUserId = (int) session('userdata.id');
        $userRole = session('userdata.role');

        // User ID 0 (or omitted) means the current user per server instructions
        $userId = (int) ($arguments['userId'] ?? 0) ?: $currentUserId;

        if ($userId !== $currentUserId && ! in_array($userRole, ['admin', 'manager', 'owner'])) {
            return ToolResult::error("You don't have permission to view other users' timesheet data.");
        }

        $fromDate = dtHelper()->parseUserDateTime($arguments['weekStart'])->startOfWeek();
        $projectId = ($arguments['projectId'] ?? null);
        $targetUserId = $userId;

        $timesheetGroups = $this->timesheetsService->getWeeklyTimesheets(
            projectId: $projectId ?? -1,
            fromDate: $fromDate,
            userId: $targetUserId
        );

        if (empty($timesheetGroups)) {
            return ToolResult::text('No timesheet entries found for the specified week.');
        }

        $weekEnd = $fromDate->addDays(6);
        $response = "## Weekly Timesheet\n";
        $response .= 'Week of '.$fromDate->formatDateForUser().' to '.$weekEnd->formatDateForUser()."\n\n";

        $response .= "| Task | Type | Mon | Tue | Wed | Thu | Fri | Sat | Sun | Total |\n";
        $response .= "|------|------|-----|-----|-----|-----|-----|-----|-----|-------|\n";

        $dailyTotals = [0, 0, 0, 0, 0, 0, 0];
        $grandTotal = 0;

        foreach ($timesheetGroups as $group) {
            $row = '| '.($group['headline'] ?? 'No task').' | '.$group['kind'].' |';

            for ($i = 1; $i <= 7; $i++) {
                $hours = $group["day{$i}"]['hours'] ?? 0;
                $row .= ' '.($hours > 0 ? number_format($hours, 1) : '-').' |';
                $dailyTotals[$i - 1] += $hours;
            }

            $row .= ' '.number_format($group['rowSum'], 1).' |';
            $grandTotal += $group['rowSum'];

            $response .= $row."\n";
        }

        $response .= '| **Daily Totals** | | ';
        foreach ($dailyTotals as $total) {
            $response .= '**'.number_format($total, 1).'** | ';
        }
        $response .= '**'.number_format($grandTotal, 1)."** |\n\n";

        return ToolResult::text($response);
    }
}
