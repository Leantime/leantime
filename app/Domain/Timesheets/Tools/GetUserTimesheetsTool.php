<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get timesheet entries for the current user.
 */
#[IsReadOnly]
class GetUserTimesheetsTool extends Tool
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
            ->integer('projectId')->description('Project ID to filter by (optional).');
    }

    public function name(): string
    {
        return 'getUserTimesheets';
    }

    public function description(): string
    {
        return 'Gets timesheet entries for the current user within a specified date range.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $fromDate = dtHelper()->parseUserDateTime($arguments['dateFrom']);
        $toDate = dtHelper()->parseUserDateTime($arguments['dateTo']);

        $userId = session('userdata.id');
        $projectId = ($arguments['projectId'] ?? null);

        $timesheets = $this->timesheetsService->getAll(
            dateFrom: $fromDate,
            dateTo: $toDate,
            projectId: $projectId ?? -1,
            userId: $userId
        );

        if (empty($timesheets)) {
            return ToolResult::text('No timesheet entries found for the specified date range.');
        }

        $response = "## Your Timesheet Entries\n";
        $response .= 'From: '.$fromDate->formatDateForUser().' to '.$toDate->formatDateForUser()."\n\n";

        $totalHours = 0;
        $projectTotals = [];

        foreach ($timesheets as $entry) {
            $entryProjectId = $entry['projectId'];
            $projectName = $entry['name'];
            $hours = (float) $entry['hours'];
            $date = dtHelper()->parseDbDateTime($entry['workDate'])->formatDateForUser();
            $ticketTitle = $entry['headline'] ?? 'No ticket';
            $description = Str::sanitizeForLLM($entry['description'] ?? '');
            $kind = $entry['kind'];

            $result = [
                'date' => $date,
                'project' => $projectName,
                'ticket' => $ticketTitle,
                'hours' => $hours,
                'type' => $kind,
                'description' => $description,
            ];

            $response .= Str::toMarkdown($result)."\n";

            $totalHours += $hours;

            if (! isset($projectTotals[$entryProjectId])) {
                $projectTotals[$entryProjectId] = [
                    'name' => $projectName,
                    'hours' => 0,
                ];
            }
            $projectTotals[$entryProjectId]['hours'] += $hours;
        }

        $response .= "\n## Summary\n";
        $response .= 'Total Hours: '.number_format($totalHours, 2)."\n\n";

        $response .= "### Hours by Project\n";
        foreach ($projectTotals as $project) {
            $response .= '- **'.$project['name'].'**: '.number_format($project['hours'], 2)." hours\n";
        }

        return ToolResult::text($response);
    }
}
