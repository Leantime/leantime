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
 * Chooses how often one to-do reminds the signed-in person.
 */
#[IsIdempotent]
class SetTaskReminderTool extends Tool
{
    use ToolErrors;

    public function __construct(
        private Reminders $reminders,
    ) {}

    public function name(): string
    {
        return 'setTaskReminder';
    }

    public function title(): string
    {
        return 'Set Task Reminder';
    }

    public function description(): string
    {
        return 'Chooses how often one to-do reminds the signed-in user in their morning summary. Presets: "default" (the day before and the morning it is due), "more" (every morning from its start date, or 3 days before, until it is due), "less" (only the morning it is due), "off" (never), or "inherit" to remove the choice so the user\'s own default applies. Works on any to-do the user can see: on their own to-dos (assigned to them, or unassigned and created by them) it changes how often they are reminded; on anyone else\'s it is an opt-in that adds the to-do to the user\'s own summary ("off" or "inherit" removes it) and never reminds the to-do\'s person. The result says isYours. Reminders need the to-do to have a due date.';
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticketId' => $schema->integer()
                ->description('ID of the to-do.')
                ->required(),
            'preset' => $schema->string()
                ->enum(['default', 'more', 'less', 'off', 'inherit'])
                ->description('How often it reminds the user.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $ticketId = (int) $request->get('ticketId', 0);
        $preset = (string) $request->get('preset', '');

        try {
            $this->reminders->setTaskPreset($ticketId, $preset);
            $state = $this->reminders->getTaskReminders($ticketId);
        } catch (LeantimeExceptionInterface $e) {
            return self::errorResponse($e);
        }

        return Response::json($state);
    }
}
