<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Start a timer for a specific duration.
 */
class StartTimerTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'duration' => $schema->string()
                ->description('Duration in format like "25m" or "1h30m".')
                ->required(),
            'taskId' => $schema->integer()
                ->description('Task ID to associate timer with.'),
            'type' => $schema->string()
                ->description('Timer type (work/break).'),
        ];
    }

    public function name(): string
    {
        return 'startTimer';
    }

    public function title(): string
    {
        return 'Start Timer';
    }

    public function description(): string
    {
        return 'Start a timer for a specific duration.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

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
            return Response::error('Invalid duration format. Use format like "25m" or "1h30m".');
        }

        if ($taskId !== null && (int) $taskId > 0) {
            $this->timesheetsService->punchIn((int) $taskId);
        }

        return Response::text("[timer startTime='".(time() + 10)."' duration='".($minutes * 60)."' type='".$type."']");
    }
}
