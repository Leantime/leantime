<?php

namespace Leantime\Domain\Reminders\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Reminders\Services\Reminders;

/**
 * The signed-in person's reminder settings.
 */
#[IsReadOnly]
class GetReminderSettingsTool extends Tool
{
    use ToolErrors;

    public function __construct(
        private Reminders $reminders,
    ) {}

    public function name(): string
    {
        return 'getReminderSettings';
    }

    public function title(): string
    {
        return 'Get Reminder Settings';
    }

    public function description(): string
    {
        return 'Gets the signed-in user\'s reminder settings: whether their morning summary is on (summary), its time as HH:MM in their timezone (summaryTime, timezone), how often to-dos remind them by default (taskPreset: default, more, less or off), and whether an admin turned reminders off for the workspace (workspaceEnabled).';
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Response
    {
        try {
            return Response::json($this->reminders->getSettings());
        } catch (LeantimeExceptionInterface $e) {
            return self::errorResponse($e);
        }
    }
}
