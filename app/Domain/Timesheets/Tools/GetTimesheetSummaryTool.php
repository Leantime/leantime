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
 * Get a summary of logged hours grouped by different criteria.
 */
#[Name('getTimesheetSummary')]
#[Description('Gets a summary of logged hours for the current user or a project, grouped by different criteria.')]
#[IsReadOnly]
class GetTimesheetSummaryTool extends Tool
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
            'dateFrom' => $schema->string()
                ->description('Start date in ISO8601 format.')
                ->required(),
            'dateTo' => $schema->string()
                ->description('End date in ISO8601 format.')
                ->required(),
            'groupBy' => $schema->string()
                ->enum(['project', 'user', 'day', 'week', 'ticket', 'kind'])
                ->description('How to group the data (project, user, day, week, ticket, kind).')
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

        $fromDate = dtHelper()->parseUserDateTime($request->string('dateFrom'));
        $toDate = dtHelper()->parseUserDateTime($request->string('dateTo'));
        $groupBy = $request->string('groupBy');
        $projectId = $request->get('projectId');
        $targetUserId = $userId ?? $currentUserId;

        $timesheets = $this->timesheetsService->getAll(
            dateFrom: $fromDate,
            dateTo: $toDate,
            projectId: $projectId ?? -1,
            kind: 'all',
            userId: $targetUserId
        );

        if (empty($timesheets)) {
            return Response::text('No timesheet entries found for the specified criteria.');
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

        return Response::text($response);
    }
}
