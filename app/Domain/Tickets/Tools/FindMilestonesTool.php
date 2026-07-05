<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Search for milestones in a project.
 */
#[IsReadOnly]
class FindMilestonesTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'findMilestones';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Gets all milestones from the database given search criteria array. All dates are returned in the format YYYY-MM-DD hh:mm:ss in the UTC timezone';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID of the milestones to retrieve.')->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $milestones = $this->ticketsService->getAllMilestones(['currentProject' => $projectId, 'type' => 'milestone']);

        $results = "## MILESTONES \n";

        if (empty($milestones)) {
            $results .= 'No Milestones found for this project.';

            return ToolResult::text($results);
        }

        foreach ($milestones as $milestone) {
            $progress = $this->ticketsService->getMilestoneProgress($milestone->id);
            $results .= 'Title: '.$milestone->headline."\n";
            $results .= 'Start Date: '.$milestone->editFrom."\n";
            $results .= 'End Date: '.$milestone->editTo."\n";
            $results .= 'Color: '.$milestone->tags."\n";
            $results .= 'Progress: '.$progress."% completed\n";
        }

        return ToolResult::text($results);
    }
}
