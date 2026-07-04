<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Search for milestones in a project.
 */
#[Name('findMilestones')]
#[Description('Gets all milestones from the database given search criteria. All dates are returned in the format YYYY-MM-DD hh:mm:ss in the UTC timezone.')]
#[IsReadOnly]
class FindMilestonesTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Project ID of the milestones to retrieve.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $milestones = $this->ticketsService->getAllMilestones(['currentProject' => $projectId, 'type' => 'milestone']);

        $results = "## MILESTONES \n";

        if (empty($milestones)) {
            $results .= 'No Milestones found for this project.';

            return Response::text($results);
        }

        foreach ($milestones as $milestone) {
            $progress = $this->ticketsService->getMilestoneProgress($milestone->id);
            $results .= 'Title: '.$milestone->headline."\n";
            $results .= 'Start Date: '.$milestone->editFrom."\n";
            $results .= 'End Date: '.$milestone->editTo."\n";
            $results .= 'Color: '.$milestone->tags."\n";
            $results .= 'Progress: '.$progress."% completed\n";
        }

        return Response::text($results);
    }
}
