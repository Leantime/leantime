<?php

namespace Leantime\Domain\Projects\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Updates an existing project with the specified details.
 */
class EditProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Modulemanager $moduleManager,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the project to update.')
            ->required()
            ->string('name')->description('Name of the project.')
            ->string('details')->description('Project description.')
            ->integer('clientId')->description('ID of the client for this project.')
            ->string('start')->description('Start date in ISO8601 format.')
            ->string('end')->description('End date in ISO8601 format.')
            ->integer('hourBudget')->description('Hour budget for the project.')
            ->integer('state')->description('Project state (0=open, 1=closed).')
            ->integer('parent')->description('ID of the parent program or plan (only works if PgmPro plugin is active).');
    }

    public function name(): string
    {
        return 'editProject';
    }

    public function description(): string
    {
        return 'Updates an existing project with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);

        // Get current project to ensure it exists
        $currentProject = $this->projectService->getProject($id);
        if (! $currentProject) {
            return ToolResult::error('Project not found.');
        }

        $values = [];

        // Only include parameters that were actually provided
        $name = ($arguments['name'] ?? null);
        if ($name !== null) {
            $values['name'] = $name;
        }

        $details = ($arguments['details'] ?? null);
        if ($details !== null) {
            $values['details'] = $details;
        }

        $clientId = ($arguments['clientId'] ?? null);
        if ($clientId !== null) {
            $values['clientId'] = $clientId;
        }

        $start = ($arguments['start'] ?? null);
        if ($start !== null) {
            $values['start'] = $start;
        }

        $end = ($arguments['end'] ?? null);
        if ($end !== null) {
            $values['end'] = $end;
        }

        $hourBudget = ($arguments['hourBudget'] ?? null);
        if ($hourBudget !== null) {
            $values['hourBudget'] = $hourBudget;
        }

        $state = ($arguments['state'] ?? null);
        if ($state !== null) {
            $values['state'] = $state;
        }

        // Check if PgmPro plugin is active and parent is specified
        $parent = ($arguments['parent'] ?? null);
        if ($parent !== null && $this->moduleManager->isModuleAvailable('pgmPro')) {
            $values['parent'] = $parent;
        }

        // If no values were provided, return early
        if (empty($values)) {
            return ToolResult::text('No changes provided for the project.');
        }

        $this->projectService->editProject($values, $id);

        return ToolResult::text('Project updated successfully.');
    }
}
