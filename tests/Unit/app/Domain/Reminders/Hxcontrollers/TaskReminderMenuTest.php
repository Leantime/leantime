<?php

namespace Unit\app\Domain\Reminders\Hxcontrollers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Reminders\Hxcontrollers\TaskReminderMenu;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * "Your reminders" in the open to-do's ⋮ menu: the picker's choices as menu items that save on
 * tap, without a second modal. Saves only on POST; the service checks the person can see it.
 */
class TaskReminderMenuTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, mixed> */
    private array $assigned = [];

    /** @var array<int, array> */
    private array $picks = [];

    private array $state = ['preset' => 'more', 'chosen' => true, 'inherited' => 'default', 'defaultPreset' => 'default', 'hasDueDate' => true, 'summary' => true, 'workspaceEnabled' => true, 'isYours' => true];

    protected function setUp(): void
    {
        parent::setUp();
        $this->assigned = [];
        $this->picks = [];

        // The view factory discovers plugin composer paths, which hits the database unit tests
        // don't have.
        $plugins = $this->createMock(\Leantime\Core\Plugins\Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(\Leantime\Core\Plugins\Plugins::class, $plugins);
    }

    private function controller(string $method, array $parameters): TaskReminderMenu
    {
        $request = $method === 'GET'
            ? IncomingRequest::create('/hx/reminders/taskReminderMenu/get', 'GET', $parameters)
            : IncomingRequest::create('/hx/reminders/taskReminderMenu/choose', $method, $parameters);
        $tpl = $this->make(Template::class, [
            'emptyResponse' => fn ($code = 200) => new Response('', $code),
            'assign' => function ($name, $value) {
                $this->assigned[$name] = $value;
            },
        ]);
        $reminders = $this->make(Reminders::class, [
            'getTaskReminders' => function (int $ticketId) {
                if ($ticketId === 2) {
                    throw new AuthorizationException;
                }

                return $this->state;
            },
            'chooseTaskReminder' => function (int $ticketId, string $preset, bool $forAll = false) {
                if ($ticketId === 2) {
                    throw new AuthorizationException;
                }
                if (! in_array($preset, ['default', 'more', 'less', 'off'], true)) {
                    throw new ValidationException(['preset' => ['Use default, more, less or off.']]);
                }
                $this->picks[] = [$ticketId, $preset, $forAll];

                return ['preset' => $preset, 'chosen' => $preset !== 'default'] + $this->state;
            },
        ]);

        $controller = (new \ReflectionClass(TaskReminderMenu::class))->newInstanceWithoutConstructor();
        foreach (['incomingRequest' => $request, 'tpl' => $tpl, 'language' => $this->make(Language::class, ['__' => fn (string $key) => $key])] as $property => $value) {
            (new \ReflectionProperty(TaskReminderMenu::class, $property))->setValue($controller, $value);
        }
        $controller->init($reminders);

        return $controller;
    }

    public function test_the_menu_shows_your_reminder_for_the_to_do(): void
    {
        $this->assertSame('menu', $this->controller('GET', ['ticketId' => 1])->get());
        $this->assertSame(1, $this->assigned['ticketId']);
        $this->assertSame('more', $this->assigned['reminder']['preset']);
    }

    public function test_a_tap_saves_and_redraws_the_menu(): void
    {
        $fragment = $this->controller('POST', ['ticketId' => '1', 'preset' => 'less'])->choose();

        $this->assertSame('menu', $fragment);
        $this->assertSame([[1, 'less', false]], $this->picks);
        $this->assertSame('less', $this->assigned['reminder']['preset']);

        $this->controller('POST', ['ticketId' => '1', 'preset' => 'more', 'forAllMyTodos' => '1'])->choose();
        $this->assertSame([1, 'more', true], $this->picks[1]);
    }

    public function test_saving_needs_post_and_a_known_choice(): void
    {
        $this->assertSame(405, $this->controller('GET', ['ticketId' => 1, 'preset' => 'off'])->choose()->getStatusCode());

        $this->controller('POST', ['ticketId' => '1', 'preset' => 'loud'])->choose();
        $this->assertSame([], $this->picks);
        $this->assertSame('reminders.picker_not_saved', $this->assigned['errorMessage']);
    }

    public function test_a_to_do_you_cannot_see_is_refused(): void
    {
        foreach ([TaskReminderMenu::class.'::get', TaskReminderMenu::class.'::choose'] as $method) {
            $attribute = (new \ReflectionMethod($method))->getAttributes(RequiresPermission::class)[0]->newInstance();
            $this->assertSame(TicketsPermissions::VIEW, $attribute->permission);
            $this->assertTrue($attribute->entityScoped, 'the service checks the to-do\'s own project');
        }

        try {
            $this->controller('POST', ['ticketId' => '2', 'preset' => 'off'])->choose();
            $this->fail('saved on a to-do the person can\'t see');
        } catch (AuthorizationException) {
            $this->assertSame([], $this->picks);
        }

        $this->expectException(AuthorizationException::class);
        $this->controller('GET', ['ticketId' => 2])->get();
    }

    private function render(array $reminder): string
    {
        return view('reminders::partials.taskReminderMenu', ['ticketId' => 5, 'reminder' => $reminder])->fragment('menu');
    }

    public function test_the_partial_marks_the_current_choice_and_your_default(): void
    {
        $html = $this->render($this->state);

        $this->assertSame(4, substr_count($html, 'role="menuitemradio"'));
        $this->assertMatchesRegularExpression('/aria-checked="true"[^>]*"preset": "more"/s', $html, 'the current choice is checked');
        $this->assertSame(1, substr_count($html, 'aria-checked="true"'));
        $this->assertSame(4, substr_count($html, 'fa fa-check tw-invisible'), 'the other three choices plus "use for all" hide their check');
        $this->assertMatchesRegularExpression('/"preset": "default"\}.*?·\s*'.preg_quote(__('reminders.picker_your_default'), '/').'/s', $html);
        $this->assertStringContainsString('"forAllMyTodos": "1"', $html, 'offered when this to-do has its own choice');
        $this->assertStringContainsString('hx-post="'.BASE_URL.'/hx/reminders/taskReminderMenu/choose"', $html);
        $this->assertStringContainsString('hx-target="#taskReminderMenu-5"', $html);
    }

    public function test_the_partial_shows_only_the_hint_without_a_due_date(): void
    {
        $html = $this->render(['hasDueDate' => false] + $this->state);

        $this->assertStringContainsString(__('reminders.picker_needs_due_date'), $html);
        $this->assertStringNotContainsString('hx-post', $html);
    }

    public function test_on_someone_else_s_to_do_off_is_the_default_and_use_for_all_is_hidden(): void
    {
        $html = $this->render(['preset' => 'off', 'chosen' => false, 'inherited' => 'off', 'isYours' => false] + $this->state);

        $this->assertMatchesRegularExpression('/aria-checked="true"[^>]*"preset": "off"/s', $html);
        $this->assertMatchesRegularExpression('/"preset": "off"\}.*?·\s*'.preg_quote(__('reminders.picker_your_default'), '/').'/s', $html);
        $this->assertStringContainsString(e(__('reminders.picker_not_yours')), $html);

        $html = $this->render(['preset' => 'more', 'chosen' => true, 'inherited' => 'off', 'isYours' => false] + $this->state);
        $this->assertStringNotContainsString('forAllMyTodos', $html, 'only for your own to-dos');
    }

    public function test_the_partial_skips_use_for_all_when_the_to_do_follows_your_default(): void
    {
        $html = $this->render(['preset' => 'default', 'chosen' => false] + $this->state);

        $this->assertStringNotContainsString('forAllMyTodos', $html);
    }
}
