<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Updates an existing project with the specified details.
 */
#[Name('editProject')]
#[Description('Updates an existing project with the specified details.')]
class EditProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Modulemanager $moduleManager,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('ID of the project to update.')
                ->required(),
            'name' => $schema->string()
                ->description('Name of the project.'),
            'details' => $schema->string()
                ->description('Project description.'),
            'clientId' => $schema->integer()
                ->description('ID of the client for this project.'),
            'start' => $schema->string()
                ->description('Start date in ISO8601 format.'),
            'end' => $schema->string()
                ->description('End date in ISO8601 format.'),
            'hourBudget' => $schema->integer()
                ->description('Hour budget for the project.'),
            'state' => $schema->integer()
                ->description('Project state (0=open, 1=closed).'),
            'parent' => $schema->integer()
                ->description('ID of the parent program or plan (only works if PgmPro plugin is active).'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->integer('id');

        // Get current project to ensure it exists
        $currentProject = $this->projectService->getProject($id);
        if (! $currentProject) {
            return Response::error('Project not found.');
        }

        $values = [];

        // Only include parameters that were actually provided
        $name = $request->get('name');
        if ($name !== null) {
            $values['name'] = $name;
        }

        $details = $request->get('details');
        if ($details !== null) {
            $values['details'] = $details;
        }

        $clientId = $request->get('clientId');
        if ($clientId !== null) {
            $values['clientId'] = $clientId;
        }

        $start = $request->get('start');
        if ($start !== null) {
            $values['start'] = $start;
        }

        $end = $request->get('end');
        if ($end !== null) {
            $values['end'] = $end;
        }

        $hourBudget = $request->get('hourBudget');
        if ($hourBudget !== null) {
            $values['hourBudget'] = $hourBudget;
        }

        $state = $request->get('state');
        if ($state !== null) {
            $values['state'] = $state;
        }

        // Check if PgmPro plugin is active and parent is specified
        $parent = $request->get('parent');
        if ($parent !== null && $this->moduleManager->isModuleAvailable('pgmPro')) {
            $values['parent'] = $parent;
        }

        // If no values were provided, return early
        if (empty($values)) {
            return Response::text('No changes provided for the project.');
        }

        $this->projectService->editProject($values, $id);

        return Response::text('Project updated successfully.');
    }
}
