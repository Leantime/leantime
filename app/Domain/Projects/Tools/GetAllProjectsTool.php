<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets all projects the current user has access to with comprehensive progress information.
 */
#[Name('getAllProjects')]
#[Description('Gets all projects the current user has access to with comprehensive progress information. This is the primary tool for project overview and should be used instead of separate getAllProjects + getProjectProgress calls. Includes project details, progress percentages, RAG status, and recent updates in a single efficient operation.')]
#[IsReadOnly]
class GetAllProjectsTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Comments $commentsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'showClosedProjects' => $schema->boolean()
                ->description('Whether to include closed projects in the results.'),
            'includeProgressDetails' => $schema->boolean()
                ->description('Whether to include detailed progress information (RAG status, completion dates, recent comments).'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $showClosedProjects = $request->get('showClosedProjects', false);
        $includeProgressDetails = $request->get('includeProgressDetails', true);

        $projects = $this->projectService->getAll($showClosedProjects ?? false);

        $response = "## All Projects Overview\n";

        if (empty($projects)) {
            return Response::text('No projects found.');
        }

        foreach ($projects as $project) {
            $result = [
                'id' => $project['id'],
                'name' => Str::sanitizeForLLM($project['name']),
                'clientName' => Str::sanitizeForLLM($project['clientName']),
                'type' => $project['type'],
                'state' => $project['state'],
                'start' => $project['start'] ?? 'Not set',
                'end' => $project['end'] ?? 'Not set',
                'progress' => isset($project['progress']['percent']) ? round($project['progress']['percent']).'%' : 'Not calculated',
            ];

            // Add detailed progress information if requested
            if ($includeProgressDetails) {
                try {
                    $progress = $this->projectService->getProjectProgress($project['id']);
                    $projectComments = $this->commentsService->getComments('project', $project['id'], 1);

                    $result['progressPercent'] = isset($progress['percent']) ? round($progress['percent']).'%' : 'Not calculated';
                    $result['estimatedCompletion'] = isset($progress['estimatedCompletionDate']) ? strip_tags($progress['estimatedCompletionDate']) : 'Not set';
                    $result['plannedCompletion'] = $progress['plannedCompletionDate'] ?? 'Not set';

                    // Add RAG status and latest update
                    if (! empty($projectComments)) {
                        $latestComment = $projectComments[0];
                        $result['ragStatus'] = $this->formatRagStatus($latestComment['status'] ?? '');
                        $result['lastUpdate'] = [
                            'date' => $latestComment['date'],
                            'status' => $this->formatRagStatus($latestComment['status'] ?? ''),
                            'message' => Str::sanitizeForLLM($latestComment['comment'] ?? ''),
                            'author' => $latestComment['firstname'].' '.$latestComment['lastname'],
                        ];
                    } else {
                        $result['ragStatus'] = 'Not set';
                        $result['lastUpdate'] = 'No updates available';
                    }
                } catch (\Exception $e) {
                    // Fallback to basic progress info if detailed fetch fails
                    $result['ragStatus'] = 'Unable to fetch';
                    $result['lastUpdate'] = 'Unable to fetch';
                }
            }

            $response .= Str::toMarkdown($result)."\n\n";
        }

        return Response::text($response);
    }

    /**
     * Format RAG status with appropriate emoji.
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
