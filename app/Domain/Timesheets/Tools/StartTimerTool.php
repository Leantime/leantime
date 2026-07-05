<?php

namespace Leantime\Domain\Timesheets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Start a timer for a specific duration.
 */
class StartTimerTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('duration')->description('Duration in format like "25m" or "1h30m".')
            ->required()
            ->integer('taskId')->description('Task ID to associate timer with.')
            ->string('type')
            ->description('Timer type (work/break).');
    }

    public function name(): string
    {
        return 'startTimer';
    }

    public function description(): string
    {
        return 'Start a timer for a specific duration.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $duration = $arguments['duration'];
        $taskId = ($arguments['taskId'] ?? null);
        $type = ($arguments['type'] ?? 'work');

        preg_match('/^(?:(\d+)h)?(?:(\d+)m)?$/', $duration, $matches);
        $minutes = 0;
        if (! empty($matches[1])) {
            $minutes += intval($matches[1]) * 60;
        }
        if (! empty($matches[2])) {
            $minutes += intval($matches[2]);
        }

        if ($minutes <= 0) {
            return ToolResult::error('Invalid duration format. Use format like "25m" or "1h30m".');
        }

        if ($taskId !== null && (int) $taskId > 0) {
            $this->timesheetsService->punchIn((int) $taskId);
        }

        return ToolResult::text("[timer startTime='".(time() + 10)."' duration='".($minutes * 60)."' type='".$type."']");
    }
}
