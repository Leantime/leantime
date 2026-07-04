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
 * Updates specific fields of an existing project.
 */
#[Name('patchProject')]
#[Description('Updates specific fields of an existing project. This is a more flexible alternative to editProject.')]
class PatchProjectTool extends Tool
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
            'params' => $schema->object()
                ->description('Key-value pairs of fields to update. Example: {"name": "New name", "state": 0}')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->integer('id');
        $params = $request->get('params');

        // Get current project to ensure it exists
        $currentProject = $this->projectService->getProject($id);
        if (! $currentProject) {
            return Response::error('Project not found.');
        }

        // Check if $params is array of arrays (AI sometimes does this)
        if (is_array($params) && ! empty($params) && isset($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        if (! is_array($params)) {
            return Response::error('The params parameter is not a valid object. Please provide an object of key-value pairs.');
        }

        // Handle parent field if PgmPro plugin is active
        if (isset($params['parent']) && ! $this->moduleManager->isModuleAvailable('pgmPro')) {
            unset($params['parent']);
        }

        $result = $this->projectService->patch($id, $params);

        if ($result) {
            return Response::text('Project updated successfully.');
        }

        return Response::error('Failed to update project. Please check the provided information.');
    }
}
