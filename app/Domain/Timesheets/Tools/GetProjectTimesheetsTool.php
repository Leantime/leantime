<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get timesheet entries for an entire project.
 */
#[Name('getProjectTimesheets')]
#[Description('Gets timesheet entries for an entire project. This is only available to managers or admins.')]
#[IsReadOnly]
class GetProjectTimesheetsTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
        private Projects $projectsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Project ID to get timesheets for.')
                ->required(),
            'dateFrom' => $schema->string()
                ->description('Start date in ISO8601 format.')
                ->required(),
            'dateTo' => $schema->string()
                ->description('End date in ISO8601 format.')
                ->required(),
            'userId' => $schema->integer()
                ->description('Filter by specific user ID (optional).'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');

        $userRole = session('userdata.role');
        if (! in_array($userRole, ['admin', 'manager']) && ! $this->projectsService->isUserProjectManager($projectId, session('userdata.id'))) {
            return Response::error("You don't have permission to view project-wide timesheet data. Only managers and admins can access this information.");
        }

        $fromDate = dtHelper()->parseUserDateTime($request->string('dateFrom'));
        $toDate = dtHelper()->parseUserDateTime($request->string('dateTo'));
        $userId = $request->get('userId');

        $timesheets = $this->timesheetsService->getAll(
            dateFrom: $fromDate,
            dateTo: $toDate,
            projectId: $projectId,
            userId: $userId
        );

        if (empty($timesheets)) {
            return Response::text('No timesheet entries found for this project in the specified date range.');
        }

        $project = $this->projectsService->getProject($projectId);

        $response = '## Project Timesheet Summary: '.$project['name']."\n";
        $response .= 'From: '.$fromDate->formatDateForUser().' to '.$toDate->formatDateForUser()."\n\n";

        $totalHours = 0;
        $userTotals = [];
        $ticketTotals = [];
        $kindTotals = [];

        foreach ($timesheets as $entry) {
            $hours = (float) $entry['hours'];
            $userName = $entry['firstname'].' '.$entry['lastname'];
            $entryUserId = $entry['userId'];
            $ticketId = $entry['ticketId'];
            $ticketTitle = $entry['headline'] ?? 'No ticket';
            $kind = $entry['kind'];

            $totalHours += $hours;

            if (! isset($userTotals[$entryUserId])) {
                $userTotals[$entryUserId] = [
                    'name' => $userName,
                    'hours' => 0,
                ];
            }
            $userTotals[$entryUserId]['hours'] += $hours;

            if (! isset($ticketTotals[$ticketId])) {
                $ticketTotals[$ticketId] = [
                    'title' => $ticketTitle,
                    'hours' => 0,
                ];
            }
            $ticketTotals[$ticketId]['hours'] += $hours;

            if (! isset($kindTotals[$kind])) {
                $kindTotals[$kind] = 0;
            }
            $kindTotals[$kind] += $hours;
        }

        $response .= '### Total Hours: '.number_format($totalHours, 2)."\n\n";

        $response .= "### Hours by Team Member\n";
        foreach ($userTotals as $user) {
            $response .= '- **'.$user['name'].'**: '.number_format($user['hours'], 2)." hours\n";
        }

        $response .= "\n### Hours by Task\n";
        foreach ($ticketTotals as $ticketId => $ticket) {
            $response .= '- **'.$ticket['title'].'**: '.number_format($ticket['hours'], 2)." hours\n";
        }

        $response .= "\n### Hours by Type\n";
        foreach ($kindTotals as $kindKey => $hours) {
            $kindLabel = $this->timesheetsService->getLoggableHourTypes()[$kindKey] ?? $kindKey;
            $response .= '- **'.$kindLabel.'**: '.number_format($hours, 2)." hours\n";
        }

        return Response::text($response);
    }
}
