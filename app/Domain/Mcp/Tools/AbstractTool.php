<?php

namespace Leantime\Domain\Mcp\Tools;

abstract class AbstractTool implements Tool
{
    public function supportsAsync(): bool
    {
        return false;
    }

    public function requiresIdempotency(): bool
    {
        return false;
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function scopeProjectId(\Leantime\Core\Mcp\Context\McpRequestContext $context, array $arguments): int
    {
        return (int) ($arguments['projectId'] ?? 0);
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'title' => $this->title(),
            'description' => $this->description(),
            'inputSchema' => $this->inputSchema(),
            'annotations' => [
                'riskLevel' => $this->riskLevel(),
                'version' => $this->version(),
                'requiredAbilities' => $this->requiredAbilities(),
                'supportsAsync' => $this->supportsAsync(),
                'requiresIdempotency' => $this->requiresIdempotency(),
            ],
        ];
    }
}
