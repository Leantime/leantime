<?php

namespace Unit\app\Domain\Reminders\Tools;

use Laravel\Mcp\Request;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Reminders\Tools\GetComingUpTool;
use Leantime\Domain\Reminders\Tools\GetReminderSettingsTool;
use Leantime\Domain\Reminders\Tools\SetTaskReminderTool;
use Leantime\Domain\Reminders\Tools\UpdateReminderSettingsTool;
use Unit\TestCase;

/**
 * MCP tools call the service directly (no RPC permission gate). The reminders service acts for the
 * signed-in person only and checks to-dos itself; the tools pass its refusals back as errors the AI
 * can read, and never act for anyone else.
 */
class ReminderToolsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, array> calls that reached the service */
    private array $calls = [];

    private function service(array $overrides = []): Reminders
    {
        return $this->make(Reminders::class, $overrides + [
            'getSummary' => function (int $limit = 5) {
                $this->calls[] = ['getSummary', $limit];

                return ['date' => '2026-11-22', 'items' => [['kind' => 'task', 'id' => 5, 'title' => 'Send the invoice', 'daysUntil' => 0]], 'dueToday' => 1];
            },
            'getSettings' => fn () => ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'UTC', 'workspaceEnabled' => true],
            'saveSettings' => function (?bool $summary = null, ?string $summaryTime = null, ?string $taskPreset = null) {
                $this->calls[] = ['saveSettings', $summary, $summaryTime, $taskPreset];
                if ($summaryTime !== null && $summaryTime !== '07:30') {
                    throw new ValidationException(['summaryTime' => ['Use a time like 08:00.']]);
                }

                return ['summary' => $summary ?? true, 'summaryTime' => $summaryTime ?? '08:00', 'taskPreset' => $taskPreset ?? 'default', 'timezone' => 'UTC', 'workspaceEnabled' => true];
            },
            'setTaskPreset' => function (int $ticketId, string $preset) {
                $this->calls[] = ['setTaskPreset', $ticketId, $preset];
                if ($ticketId === 2) {
                    throw new AuthorizationException;
                }

                return true;
            },
            'getTaskReminders' => fn (int $ticketId) => ['preset' => 'more', 'chosen' => true],
        ]);
    }

    public function test_get_coming_up_returns_the_summary_as_data(): void
    {
        $result = (new GetComingUpTool($this->service()))->handle(new Request([]));

        $this->assertFalse($result->isError());
        $this->assertSame('getComingUp', (new GetComingUpTool($this->service()))->name());
        $this->assertStringContainsString('"title":"Send the invoice"', (string) $result->content());
    }

    public function test_get_coming_up_takes_a_limit_and_0_is_the_whole_list(): void
    {
        $tool = new GetComingUpTool($this->service());

        $tool->handle(new Request([]));
        $tool->handle(new Request(['limit' => 0]));
        $tool->handle(new Request(['limit' => 12]));

        $this->assertSame([['getSummary', 5], ['getSummary', 0], ['getSummary', 12]], $this->calls);
        $this->assertArrayHasKey('limit', $tool->schema(new \Illuminate\JsonSchema\JsonSchemaTypeFactory));
    }

    public function test_set_task_reminder_saves_and_returns_what_applies(): void
    {
        $result = (new SetTaskReminderTool($this->service()))->handle(new Request(['ticketId' => 1, 'preset' => 'more']));

        $this->assertFalse($result->isError());
        $this->assertSame([['setTaskPreset', 1, 'more']], $this->calls);
        $this->assertStringContainsString('"preset":"more"', (string) $result->content());
    }

    public function test_set_task_reminder_refuses_a_to_do_the_person_cannot_see(): void
    {
        $result = (new SetTaskReminderTool($this->service()))->handle(new Request(['ticketId' => 2, 'preset' => 'off']));

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->content());
    }

    public function test_settings_round_trip_and_bad_input_comes_back_as_a_readable_error(): void
    {
        $read = (new GetReminderSettingsTool($this->service()))->handle(new Request([]));
        $this->assertStringContainsString('"summaryTime":"08:00"', (string) $read->content());

        $saved = (new UpdateReminderSettingsTool($this->service()))->handle(new Request(['summaryTime' => '07:30']));
        $this->assertFalse($saved->isError());
        $this->assertSame(['saveSettings', null, '07:30', null], $this->calls[0], 'values left out stay as they are');

        $bad = (new UpdateReminderSettingsTool($this->service()))->handle(new Request(['summaryTime' => '7.30pm']));
        $this->assertTrue($bad->isError());
        $this->assertStringContainsString('Use a time like 08:00.', (string) $bad->content());
    }

    public function test_settings_tools_take_no_user_id(): void
    {
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory;
        foreach ([new UpdateReminderSettingsTool($this->service()), new SetTaskReminderTool($this->service()), new GetComingUpTool($this->service())] as $tool) {
            $this->assertArrayNotHasKey('userId', $tool->schema($schema), $tool->name().' only acts for the signed-in person');
        }
    }
}
