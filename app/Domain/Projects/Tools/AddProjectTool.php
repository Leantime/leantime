<?php

namespace Leantime\Domain\Projects\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Creates a new project with the specified details.
 */
class AddProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private Modulemanager $moduleManager,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('name')->description('Name of the project.')
            ->required()
            ->integer('clientId')->description('ID of the client for this project.')
            ->required()
            ->string('details')->description('Project description.')
            ->string('start')->description('Start date in ISO 8601 format.')
            ->string('end')->description('End date in ISO 8601 format.')
            ->integer('hourBudget')->description('Hour budget for the project.')
            ->integer('parent')->description('ID of the parent program or plan (only works if PgmPro plugin is active).');
    }

    public function name(): string
    {
        return 'addProject';
    }

    public function description(): string
    {
        return 'Creates a new project with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $values = [
            'name' => $arguments['name'],
            'details' => ($arguments['details'] ?? ''),
            'clientId' => (int) ($arguments['clientId'] ?? 0),
            'hourBudget' => ($arguments['hourBudget'] ?? null),
            'start' => ($arguments['start'] ?? null),
            'end' => ($arguments['end'] ?? null),
        ];

        // Check if PgmPro plugin is active and parent is specified
        $parent = ($arguments['parent'] ?? null);
        if ($parent && $this->moduleManager->isModuleAvailable('pgmPro')) {
            $values['parent'] = $parent;
        }

        $projectId = $this->projectService->addProject($values);

        if ($projectId) {
            return ToolResult::text("Project created successfully with ID: $projectId");
        }

        return ToolResult::error('Failed to create project. Please check the provided information.');
    }
}
