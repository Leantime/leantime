<?php

namespace Leantime\Domain\Reminders\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Reminders\Services\Reminders;

/**
 * Changes the signed-in person's reminder settings.
 */
#[IsIdempotent]
class UpdateReminderSettingsTool extends Tool
{
    use ToolErrors;

    public function __construct(
        private Reminders $reminders,
    ) {}

    public function name(): string
    {
        return 'updateReminderSettings';
    }

    public function title(): string
    {
        return 'Update Reminder Settings';
    }

    public function description(): string
    {
        return 'Changes the signed-in user\'s own reminder settings. Leave a value out to keep it. summary turns their morning summary on or off; summaryTime is HH:MM in their timezone (for example 08:00); taskPreset is how often to-dos remind them by default: "default" (the day before and the morning it is due), "more", "less" or "off". Returns the settings after the change.';
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->boolean()
                ->description('Morning summary on (true) or off (false).'),
            'summaryTime' => $schema->string()
                ->description('Time of the morning summary, HH:MM in the user\'s timezone.'),
            'taskPreset' => $schema->string()
                ->enum(['default', 'more', 'less', 'off'])
                ->description('How often to-dos remind the user by default.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        try {
            $settings = $this->reminders->saveSettings(
                array_key_exists('summary', $arguments) ? (bool) $arguments['summary'] : null,
                array_key_exists('summaryTime', $arguments) ? (string) $arguments['summaryTime'] : null,
                array_key_exists('taskPreset', $arguments) ? (string) $arguments['taskPreset'] : null,
            );
        } catch (LeantimeExceptionInterface $e) {
            return self::errorResponse($e);
        }

        return Response::json($settings);
    }
}
