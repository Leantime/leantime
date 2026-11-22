<?php

namespace Unit\app\Domain\Reminders\Controllers;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Reminders\Controllers\TaskReminder;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * "Remind me" from a to-do's menu: shows the person's own reminder for that to-do and saves their
 * pick. The service checks the person can see the to-do.
 */
class TaskReminderTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, mixed> */
    private array $assigned = [];

    /** @var array<int, array> */
    private array $picks = [];

    /** @var array<int, array{0: string, 1: string}> */
    private array $notifications = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->assigned = [];
        $this->picks = [];
        $this->notifications = [];
        unset($_GET['id'], $_GET['closeModal']);
    }

    protected function tearDown(): void
    {
        unset($_GET['id'], $_GET['closeModal']);
        parent::tearDown();
    }

    private function controller(): TaskReminder
    {
        $tpl = $this->make(Template::class, [
            'assign' => function ($name, $value) {
                $this->assigned[$name] = $value;
            },
            'displayPartial' => fn (string $template, int $responseCode = 200) => new Response($template, $responseCode),
            'setNotification' => function ($message, $type) {
                $this->notifications[] = [$message, $type];
            },
        ]);

        $reminders = $this->make(Reminders::class, [
            'getTaskReminders' => function (int $ticketId) {
                if ($ticketId === 2) {
                    throw new AuthorizationException;
                }

                return ['preset' => 'default', 'chosen' => false, 'inherited' => 'default', 'defaultPreset' => 'default', 'hasDueDate' => true, 'summary' => true, 'workspaceEnabled' => true];
            },
            'chooseTaskReminder' => function (int $ticketId, string $preset, bool $forAll = false) {
                if (! in_array($preset, ['default', 'more', 'less', 'off'], true)) {
                    throw new ValidationException(['preset' => ['Use default, more, less or off.']]);
                }
                $this->picks[] = [$ticketId, $preset, $forAll];

                return [];
            },
        ]);

        $tickets = $this->make(TicketRepository::class, [
            'getTicket' => function ($id) {
                if (! in_array((int) $id, [1, 2], true)) {
                    return false;
                }
                $ticket = new TicketModel;
                $ticket->id = (int) $id;
                $ticket->headline = 'Send the invoice';

                return $ticket;
            },
        ]);

        $controller = (new \ReflectionClass(TaskReminder::class))->newInstanceWithoutConstructor();
        foreach (['tpl' => $tpl, 'language' => $this->make(Language::class, ['__' => fn (string $key) => $key])] as $property => $value) {
            (new \ReflectionProperty(TaskReminder::class, $property))->setValue($controller, $value);
        }
        $controller->init($reminders, $tickets);

        return $controller;
    }

    public function test_the_picker_shows_your_reminder_for_the_to_do(): void
    {
        $response = $this->controller()->get(['id' => 1]);

        $this->assertSame('reminders.taskReminder', $response->getContent());
        $this->assertSame(1, $this->assigned['ticket']->id);
        $this->assertSame('default', $this->assigned['reminder']['preset']);
    }

    public function test_a_to_do_you_cannot_see_is_refused_and_an_unknown_one_is_not_found(): void
    {
        $this->assertSame(404, $this->controller()->get(['id' => 99])->getStatusCode());

        $this->expectException(AuthorizationException::class);
        $this->controller()->get(['id' => 2]);
    }

    public function test_saving_passes_the_pick_and_closes_the_modal(): void
    {
        $_GET['id'] = '1';

        $response = $this->controller()->post(['preset' => 'more', 'forAllMyTodos' => '1']);

        $this->assertSame([[1, 'more', true]], $this->picks);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/reminders/taskReminder/1?closeModal=true', $response->getTargetUrl());
        $this->assertSame('success', $this->notifications[0][1]);
    }

    public function test_a_bad_pick_keeps_the_modal_open_with_an_error(): void
    {
        $_GET['id'] = '1';

        $response = $this->controller()->post(['preset' => 'loud']);

        $this->assertSame([], $this->picks);
        $this->assertStringEndsWith('/reminders/taskReminder/1', $response->getTargetUrl());
        $this->assertSame('error', $this->notifications[0][1]);
    }
}
