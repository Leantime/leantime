<?php

namespace Leantime\Domain\Projects\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Updates specific fields of an existing project.
 */
class PatchProjectTool extends Tool
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
            ->raw('params', ['type' => 'object', 'description' => 'Key-value pairs of fields to update. Example: {"name": "New name", "state": 0}'])->required();
    }

    public function name(): string
    {
        return 'patchProject';
    }

    public function description(): string
    {
        return 'Updates specific fields of an existing project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        $params = ($arguments['params'] ?? null);

        // Get current project to ensure it exists
        $currentProject = $this->projectService->getProject($id);
        if (! $currentProject) {
            return ToolResult::error('Project not found.');
        }

        // Check if $params is array of arrays (AI sometimes does this)
        if (is_array($params) && ! empty($params) && isset($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        if (! is_array($params)) {
            return ToolResult::error('The params parameter is not a valid object. Please provide an object of key-value pairs.');
        }

        // Handle parent field if PgmPro plugin is active
        if (isset($params['parent']) && ! $this->moduleManager->isModuleAvailable('pgmPro')) {
            unset($params['parent']);
        }

        $result = $this->projectService->patch($id, $params);

        if ($result) {
            return ToolResult::text('Project updated successfully.');
        }

        return ToolResult::error('Failed to update project. Please check the provided information.');
    }
}
