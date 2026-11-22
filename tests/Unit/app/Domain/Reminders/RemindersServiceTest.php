<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Reminders\Services\SummaryBuilder;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * The reminders API acts for the signed-in person only, and only on to-dos they can see. Choices
 * are user settings: a choice for one to-do is stored only when it differs from "inherit".
 * Runs the real settings repository on SQLite.
 */
class RemindersServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** @var array<int, int> project ids the session user may view to-dos in */
    private array $viewable = [1];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 7]]);
        $this->createReminderDatabase();
        $this->addUser(7);
        $this->viewable = [1];
    }

    private function service(): Reminders
    {
        $config = $this->make(Environment::class);
        $config->defaultTimezone = 'UTC';

        return new Reminders(
            new ReminderSettings($this->settingRepository(), $config),
            $this->make(SummaryBuilder::class, ['build' => fn (int $userId, $now, string $timezone, string $preset) => ['for' => $userId, 'preset' => $preset]]),
            $this->make(TicketRepository::class, [
                'getTicket' => function ($id) {
                    if (! in_array((int) $id, [1, 2], true)) {
                        return false;
                    }
                    $ticket = new TicketModel;
                    $ticket->id = (int) $id;
                    $ticket->projectId = (int) $id; // to-do 1 is in project 1, to-do 2 in project 2

                    return $ticket;
                },
            ]),
            $this->make(PermissionService::class, [
                'authorize' => function (string $permission, ?int $projectId = null) {
                    if ($permission !== 'tickets.view' || ! in_array($projectId, $this->viewable, true)) {
                        throw new AuthorizationException;
                    }
                },
            ]),
        );
    }

    /** @return array<string, string> the session user's reminder keys in zp_settings */
    private function storedKeys(): array
    {
        return $this->db->table('zp_settings')->where('key', 'like', 'usersettings.7.reminders.%')->orderBy('key')->pluck('value', 'key')->all();
    }

    public function test_everything_needs_a_signed_in_person(): void
    {
        session(['userdata' => []]);

        $this->expectException(AuthorizationException::class);
        $this->service()->getSummary();
    }

    public function test_the_summary_is_always_the_session_user_s(): void
    {
        $this->assertSame(['for' => 7, 'preset' => 'default'], $this->service()->getSummary());
    }

    public function test_settings_are_validated_and_saved_as_user_settings(): void
    {
        $result = $this->service()->saveSettings(summary: false, summaryTime: '07:30', taskPreset: 'more');

        $this->assertSame([
            'usersettings.7.reminders.summary' => '0',
            'usersettings.7.reminders.summaryTime' => '07:30',
            'usersettings.7.reminders.taskPreset' => 'more',
        ], $this->storedKeys());
        $this->assertSame(['summary' => false, 'summaryTime' => '07:30', 'taskPreset' => 'more', 'timezone' => 'UTC', 'workspaceEnabled' => true], $result);

        foreach ([['summaryTime' => '7:30'], ['summaryTime' => '24:00'], ['taskPreset' => 'loud']] as $bad) {
            try {
                $this->service()->saveSettings(...$bad);
                $this->fail('accepted '.json_encode($bad));
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_a_task_choice_round_trips_through_user_settings_and_inherit_deletes_it(): void
    {
        $service = $this->service();
        $service->saveSettings(taskPreset: 'less');

        $this->assertSame(['preset' => 'less', 'chosen' => false], $service->getTaskReminders(1));
        $this->assertArrayNotHasKey('usersettings.7.reminders.task.1', $this->storedKeys(), 'nothing stored until a choice is made');

        $service->setTaskPreset(1, 'more');
        $this->assertSame('more', $this->storedKeys()['usersettings.7.reminders.task.1']);
        $this->assertSame(['preset' => 'more', 'chosen' => true], $this->service()->getTaskReminders(1));
        $this->assertSame([1 => 'more'], $this->repository()->taskChoices(7), 'the builder reads it with the prefix query');

        $service->setTaskPreset(1, 'inherit');
        $this->assertArrayNotHasKey('usersettings.7.reminders.task.1', $this->storedKeys(), 'inherit deletes the key');
        $this->assertSame(['preset' => 'less', 'chosen' => false], $this->service()->getTaskReminders(1));
        $this->assertSame([], $this->repository()->taskChoices(7));
    }

    public function test_task_methods_check_the_person_can_see_the_task(): void
    {
        $service = $this->service();

        foreach ([fn () => $service->getTaskReminders(2), fn () => $service->setTaskPreset(2, 'off')] as $call) {
            try {
                $call();
                $this->fail('a to-do in a project the person can\'t see');
            } catch (AuthorizationException) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame([], $this->storedKeys());

        $this->expectException(NotFoundException::class);
        $service->getTaskReminders(404);
    }

    public function test_an_unknown_preset_is_refused(): void
    {
        $this->expectException(ValidationException::class);
        $this->service()->setTaskPreset(1, 'loud');
    }
}
