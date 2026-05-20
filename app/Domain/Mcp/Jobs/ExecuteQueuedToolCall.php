<?php

namespace Leantime\Domain\Mcp\Jobs;

use Leantime\Domain\Mcp\Services\ToolExecutor;

class ExecuteQueuedToolCall
{
    public function handle(array $payload): bool
    {
        return app()->make(ToolExecutor::class)->executeQueuedToolCall((int) ($payload['toolCallId'] ?? 0));
    }
}
