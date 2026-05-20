<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;

interface Tool
{
    public function name(): string;

    public function title(): string;

    public function description(): string;

    public function inputSchema(): array;

    public function requiredAbilities(): array;

    public function riskLevel(): string;

    public function supportsAsync(): bool;

    public function requiresIdempotency(): bool;

    public function version(): string;

    public function scopeProjectId(McpRequestContext $context, array $arguments): int;

    public function definition(): array;

    public function execute(McpRequestContext $context, array $arguments): array;
}
