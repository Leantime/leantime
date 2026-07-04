<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get timesheet entries for the current user.
 */
#[Name('getUserTimesheets')]
#[Description('Gets timesheet entries for the current user within a specified date range. This allows users to see their logged time across projects.')]
#[IsReadOnly]
class GetUserTimesheetsTool extends Tool
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
            'projectId' => $schema->integer()
                ->description('Project ID to filter by (optional).'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $fromDate = dtHelper()->parseUserDateTime($request->string('dateFrom'));
        $toDate = dtHelper()->parseUserDateTime($request->string('dateTo'));

        $userId = session('userdata.id');
        $projectId = $request->get('projectId');

        $timesheets = $this->timesheetsService->getAll(
            dateFrom: $fromDate,
            dateTo: $toDate,
            projectId: $projectId ?? -1,
            userId: $userId
        );

        if (empty($timesheets)) {
            return Response::text('No timesheet entries found for the specified date range.');
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

        return Response::text($response);
    }
}
