<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Creates a new project with the specified details.
 */
#[IsDestructive(false)]
class AddProjectTool extends Tool
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
            'name' => $schema->string()
                ->description('Name of the project.')
                ->required(),
            'clientId' => $schema->integer()
                ->description('ID of the client for this project.')
                ->required(),
            'details' => $schema->string()
                ->description('Project description.'),
            'start' => $schema->string()
                ->description('Start date in ISO 8601 format.'),
            'end' => $schema->string()
                ->description('End date in ISO 8601 format.'),
            'hourBudget' => $schema->integer()
                ->description('Hour budget for the project.'),
            'parent' => $schema->integer()
                ->description('ID of the parent program or plan (only works if PgmPro plugin is active).'),
        ];
    }

    public function name(): string
    {
        return 'addProject';
    }

    public function title(): string
    {
        return 'Add Project';
    }

    public function description(): string
    {
        return 'Creates a new project with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

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
            return Response::text("Project created successfully with ID: $projectId");
        }

        return Response::error('Failed to create project. Please check the provided information.');
    }
}
