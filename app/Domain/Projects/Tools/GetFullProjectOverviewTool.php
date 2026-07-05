<?php

namespace Leantime\Domain\Projects\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Gets comprehensive project information in a single call.
 */
#[IsReadOnly]
class GetFullProjectOverviewTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Comments $commentsService,
        private Tickets $ticketsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID to get full overview for.')
            ->required()
            ->boolean('includeTimesheets')->description('Whether to include timesheet data in the overview. Default false.')
            ->string('dateFrom')->description('Start date for timesheet data if included. ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
            ->string('dateTo')->description('End date for timesheet data if included. ISO8601 format (example: 2024-04-30T15:00:00-04:00).');
    }

    public function name(): string
    {
        return 'getFullProjectOverview';
    }

    public function description(): string
    {
        return 'Gets comprehensive project information in a single call, combining project details, progress, comments, and optionally timesheets.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $includeTimesheets = $request->get('includeTimesheets', false);
        $dateFrom = ($arguments['dateFrom'] ?? '');
        $dateTo = ($arguments['dateTo'] ?? '');

        // This method consolidates what would normally be 3-4 separate tool calls
        $response = "# Complete Project Overview\n\n";

        try {
            // Get project details (equivalent to getProject tool)
            $project = $this->projectService->getProject($projectId);
            if (! $project) {
                return ToolResult::error("Project with ID {$projectId} not found.");
            }

            $response .= "## Project Details\n";
            $response .= "**Name:** {$project['name']}\n";
            $response .= '**Client:** '.($project['clientName'] ?? 'No client')."\n";
            $response .= "**Type:** {$project['type']}\n";
            $response .= '**Status:** '.($project['state'] ?? 'Not set')."\n";
            $response .= '**Start Date:** '.($project['start'] ?? 'Not set')."\n";
            $response .= '**End Date:** '.($project['end'] ?? 'Not set')."\n";
            $response .= "**Description:** {$project['details']}\n\n";

            // Get project progress (equivalent to getProjectProgress tool)
            $progress = $this->projectService->getProjectProgress($projectId);

            $response .= "## Progress Overview\n";
            $response .= '**Overall Progress:** '.($progress['percent'] ?? '0')."%\n";
            $response .= '**RAG Status:** '.$this->formatRagStatus($progress['ragStatus'] ?? '')."\n";
            $response .= '**Estimated Completion:** '.($progress['estimatedCompletionDate'] ?? 'Not calculated')."\n";
            $response .= '**Planned Completion:** '.($progress['plannedCompletionDate'] ?? 'Not set')."\n\n";

            // Get recent status updates/comments (equivalent to getAllProjectComments tool)
            $comments = $this->commentsService->getComments('project', $projectId);

            $response .= "## Recent Status Updates\n";
            if (empty($comments)) {
                $response .= "*No status updates found.*\n\n";
            } else {
                foreach ($comments as $comment) {
                    $status = $this->formatRagStatus($comment['status'] ?? '');
                    $response .= "**{$comment['date']}** - {$status}\n";
                    $response .= "*{$comment['firstname']} {$comment['lastname']}:* {$comment['comment']}\n\n";
                }
            }

            // Optionally include timesheet data
            if ($includeTimesheets) {
                $response .= "## Time Tracking Summary\n";

                // Set default date range if not provided
                if (empty($dateFrom)) {
                    $dateFrom = date('Y-m-01'); // First day of current month
                }
                if (empty($dateTo)) {
                    $dateTo = date('Y-m-d'); // Today
                }

                try {
                    $timesheets = app(Timesheets::class)->getProjectTimesheets($projectId, $dateFrom, $dateTo);

                    if (empty($timesheets)) {
                        $response .= "*No timesheet entries found for the specified period.*\n\n";
                    } else {
                        $totalHours = 0;
                        $userHours = [];

                        foreach ($timesheets as $entry) {
                            $hours = floatval($entry['hours'] ?? 0);
                            $totalHours += $hours;
                            $user = $entry['firstname'].' '.$entry['lastname'];
                            $userHours[$user] = ($userHours[$user] ?? 0) + $hours;
                        }

                        $response .= "**Total Hours Logged:** {$totalHours}h\n";
                        $response .= "**Period:** {$dateFrom} to {$dateTo}\n";
                        $response .= "**Team Breakdown:**\n";
                        foreach ($userHours as $user => $hours) {
                            $response .= "- {$user}: {$hours}h\n";
                        }
                        $response .= "\n";
                    }
                } catch (\Exception $e) {
                    $response .= "*Could not retrieve timesheet data.*\n\n";
                }
            }

            // Add quick task summary
            $response .= "## Task Summary\n";
            try {
                $allTasks = $this->ticketsService->getAll(['currentProject' => $projectId], 100);
                $taskStats = $this->calculateTaskStats($allTasks);

                $response .= "**Total Tasks:** {$taskStats['total']}\n";
                $response .= "**Completed:** {$taskStats['completed']} ({$taskStats['completedPercent']}%)\n";
                $response .= "**In Progress:** {$taskStats['inProgress']}\n";
                $response .= "**Not Started:** {$taskStats['notStarted']}\n";
                $response .= "**Overdue:** {$taskStats['overdue']}\n\n";

            } catch (\Exception $e) {
                $response .= "*Could not retrieve task statistics.*\n\n";
            }

            return ToolResult::text($response);

        } catch (\Exception $e) {
            return ToolResult::error('Error retrieving project overview: '.$e->getMessage());
        }
    }

    /**
     * Calculate task statistics from a list of tasks.
     *
     * @param  array  $tasks  Array of task data.
     * @return array<string, int|float> Computed statistics.
     */
    private function calculateTaskStats(array $tasks): array
    {
        $stats = [
            'total' => count($tasks),
            'completed' => 0,
            'inProgress' => 0,
            'notStarted' => 0,
            'overdue' => 0,
            'completedPercent' => 0,
        ];

        $now = new \DateTime;

        foreach ($tasks as $task) {
            $status = $task['status'] ?? '';
            $dueDate = $task['dateToFinish'] ?? '';

            // Count by status type
            if (in_array($status, ['done', 'closed', 'completed'])) {
                $stats['completed']++;
            } elseif (in_array($status, ['inprogress', 'working', 'development'])) {
                $stats['inProgress']++;
            } else {
                $stats['notStarted']++;
            }

            // Check for overdue tasks
            if (! empty($dueDate) && ! in_array($status, ['done', 'closed', 'completed'])) {
                $due = new \DateTime($dueDate);
                if ($due < $now) {
                    $stats['overdue']++;
                }
            }
        }

        if ($stats['total'] > 0) {
            $stats['completedPercent'] = round(($stats['completed'] / $stats['total']) * 100, 1);
        }

        return $stats;
    }

    /**
     * Format RAG status with appropriate label.
     */
    private function formatRagStatus(string $status): string
    {
        return match (strtolower($status)) {
            'green' => 'Green (On Track)',
            'yellow' => 'Yellow (At Risk)',
            'red' => 'Red (Critical)',
            default => $status ?: 'Not Set'
        };
    }
}
