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
 * Creates a new project with the specified details.
 */
#[Name('addProject')]
#[Description('Creates a new project with the specified details. If PgmPro plugin is active, can nest projects under a plan or program.')]
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

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $values = [
            'name' => $request->string('name'),
            'details' => $request->string('details', ''),
            'clientId' => $request->integer('clientId'),
            'hourBudget' => $request->get('hourBudget'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
        ];

        // Check if PgmPro plugin is active and parent is specified
        $parent = $request->get('parent');
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
