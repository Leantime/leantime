<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Get timesheet entries for an entire project.
 */
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

    public function name(): string
    {
        return 'getProjectTimesheets';
    }

    public function title(): string
    {
        return 'Get Project Timesheets';
    }

    public function description(): string
    {
        return 'Gets timesheet entries for an entire project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = (int) ($arguments['projectId'] ?? 0);

        $globalRole = session('userdata.role');
        if (! Roles::isAtLeast($globalRole, Roles::$manager)) {
            // getProjectRole() returns a stored role key (usually a numeric string like "30");
            // resolve it to a role name before comparing (same pattern as
            // Tickets::userIsAtLeastForProject).
            $projectRoleKey = $this->projectsService->getProjectRole(session('userdata.id'), $projectId);
            $projectRole = ctype_digit((string) $projectRoleKey)
                ? Roles::getRoleString((int) $projectRoleKey)
                : $projectRoleKey;

            if ($projectRole === false || ! Roles::isAtLeast($projectRole, Roles::$manager)) {
                return Response::error("You don't have permission to view project-wide timesheet data. This requires a manager role or above, or a manager role in this project.");
            }
        }

        $fromDate = dtHelper()->parseUserDateTime($arguments['dateFrom']);
        $toDate = dtHelper()->parseUserDateTime($arguments['dateTo']);
        $userId = ($arguments['userId'] ?? null);

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
