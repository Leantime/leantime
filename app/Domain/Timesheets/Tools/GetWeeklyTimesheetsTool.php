<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get a weekly view of timesheet entries.
 */
#[Name('getWeeklyTimesheets')]
#[Description('Gets a weekly view of timesheet entries for the current user or a project.')]
#[IsReadOnly]
class GetWeeklyTimesheetsTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'weekStart' => $schema->string()
                ->description('Start date of the week in ISO8601 format.')
                ->required(),
            'projectId' => $schema->integer()
                ->description('Project ID to filter by (optional).'),
            'userId' => $schema->integer()
                ->description('User ID to filter by (optional, managers only).'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $currentUserId = session('userdata.id');
        $userRole = session('userdata.role');
        $userId = $request->get('userId');

        if ($userId !== null && $userId !== $currentUserId && ! in_array($userRole, ['admin', 'manager'])) {
            return Response::error("You don't have permission to view other users' timesheet data.");
        }

        $fromDate = dtHelper()->parseUserDateTime($request->string('weekStart'))->startOfWeek();
        $projectId = $request->get('projectId');
        $targetUserId = $userId ?? $currentUserId;

        $timesheetGroups = $this->timesheetsService->getWeeklyTimesheets(
            projectId: $projectId ?? -1,
            fromDate: $fromDate,
            userId: $targetUserId
        );

        if (empty($timesheetGroups)) {
            return Response::text('No timesheet entries found for the specified week.');
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

        return Response::text($response);
    }
}
