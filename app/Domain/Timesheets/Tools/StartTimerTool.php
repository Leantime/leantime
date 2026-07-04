<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Start a timer for a specific duration.
 */
#[Name('startTimer')]
#[Description('Start a timer for a specific duration, optionally associated with a task. The tool will return a code snippet (bbcode) that needs to be included in the response to the user so that a timer can be rendered in the frontend. The timer snippet will include startTime, duration and type. Add the snippet to the end of the response (but before actions prompts) to ensure the user can see the timer.')]
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
                ->enum(['work', 'break'])
                ->description('Timer type (work/break).')
                ->default('work'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $duration = $request->string('duration');
        $taskId = $request->get('taskId');
        $type = $request->string('type', 'work');

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
