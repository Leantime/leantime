<?php

namespace Leantime\Domain\Timesheets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get a summary of logged hours grouped by different criteria.
 */
#[IsReadOnly]
class GetTimesheetSummaryTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('dateFrom')->description('Start date in ISO8601 format.')
            ->required()
            ->string('dateTo')->description('End date in ISO8601 format.')
            ->required()
            ->string('groupBy')
            ->description('How to group the data (project, user, day, week, ticket, kind).')
            ->required()
            ->integer('projectId')->description('Project ID to filter by (optional).')
            ->integer('userId')->description('User ID to filter by (optional, managers only).');
    }

    public function name(): string
    {
        return 'getTimesheetSummary';
    }

    public function description(): string
    {
        return 'Gets a summary of logged hours grouped by different criteria.';
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

        $fromDate = dtHelper()->parseUserDateTime($arguments['dateFrom']);
        $toDate = dtHelper()->parseUserDateTime($arguments['dateTo']);
        $groupBy = $arguments['groupBy'];
        $projectId = ($arguments['projectId'] ?? null);
        $targetUserId = $userId;

        $timesheets = $this->timesheetsService->getAll(
            dateFrom: $fromDate,
            dateTo: $toDate,
            projectId: $projectId ?? -1,
            kind: 'all',
            userId: $targetUserId
        );

        if (empty($timesheets)) {
            return ToolResult::text('No timesheet entries found for the specified criteria.');
        }

        $response = "## Timesheet Summary\n";
        $response .= 'From: '.$fromDate->formatDateForUser().' to '.$toDate->formatDateForUser()."\n\n";

        $totalHours = 0;
        $groupedData = [];

        foreach ($timesheets as $entry) {
            $hours = (float) $entry['hours'];
            $totalHours += $hours;

            $groupKey = '';
            $groupLabel = '';

            switch ($groupBy) {
                case 'project':
                    $groupKey = $entry['projectId'];
                    $groupLabel = $entry['name'];
                    break;
                case 'user':
                    $groupKey = $entry['userId'];
                    $groupLabel = $entry['firstname'].' '.$entry['lastname'];
                    break;
                case 'day':
                    $date = dtHelper()->parseDbDateTime($entry['workDate']);
                    $groupKey = $date->format('Y-m-d');
                    $groupLabel = $date->formatDateForUser();
                    break;
                case 'week':
                    $date = dtHelper()->parseDbDateTime($entry['workDate']);
                    $weekStart = $date->startOfWeek()->format('Y-m-d');
                    $groupKey = $weekStart;
                    $groupLabel = 'Week of '.$date->startOfWeek()->formatDateForUser();
                    break;
                case 'ticket':
                    $groupKey = $entry['ticketId'];
                    $groupLabel = $entry['headline'] ?? 'No ticket';
                    break;
                case 'kind':
                    $groupKey = $entry['kind'];
                    $kindLabel = $this->timesheetsService->getLoggableHourTypes()[$entry['kind']] ?? $entry['kind'];
                    $groupLabel = $kindLabel;
                    break;
                default:
                    $groupKey = 'all';
                    $groupLabel = 'All Entries';
            }

            if (! isset($groupedData[$groupKey])) {
                $groupedData[$groupKey] = [
                    'label' => $groupLabel,
                    'hours' => 0,
                ];
            }
            $groupedData[$groupKey]['hours'] += $hours;
        }

        uasort($groupedData, function ($a, $b) {
            return $b['hours'] <=> $a['hours'];
        });

        $response .= '### Total Hours: '.number_format($totalHours, 2)."\n\n";

        $response .= '### Hours by '.ucfirst($groupBy)."\n";
        foreach ($groupedData as $group) {
            $percentage = ($group['hours'] / $totalHours) * 100;
            $response .= '- **'.$group['label'].'**: '.number_format($group['hours'], 2).
                         ' hours ('.number_format($percentage, 1)."%)\n";
        }

        return ToolResult::text($response);
    }
}
