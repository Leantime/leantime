<?php

namespace Leantime\Domain\Mcp\Services;

use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Tools\Tool;

class ToolRegistry
{
    public function __construct(private array $toolClasses) {}

    /**
     * @return Tool[]
     */
    public function all(): array
    {
        return array_map(fn (string $toolClass) => app()->make($toolClass), $this->toolClasses);
    }

    public function find(string $toolName): Tool
    {
        foreach ($this->all() as $tool) {
            if ($tool->name() === $toolName) {
                return $tool;
            }
        }

        throw new McpException("Unknown tool: {$toolName}", -32601, 404);
    }

    public function definitions(): array
    {
        return array_map(fn (Tool $tool) => $tool->definition(), $this->all());
    }
}
