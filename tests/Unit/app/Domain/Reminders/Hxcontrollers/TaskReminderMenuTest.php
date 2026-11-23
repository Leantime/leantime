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

    private function render(array $reminder, array $extra = []): string
    {
        return view('reminders::partials.taskReminderMenu', ['ticketId' => 5, 'instance' => 'abc123', 'reminder' => $reminder] + $extra)->fragment('menu');
    }

    public function test_the_choices_are_a_labelled_radio_group(): void
    {
        $html = $this->render($this->state);

        $this->assertStringContainsString('id="taskReminderMenu-5-abc123"', $html);
        $this->assertStringContainsString('role="radiogroup" aria-labelledby="taskReminderMenu-5-abc123-label"', $html, 'named by the menu\'s "Your reminders" heading');
        $this->assertSame(4, substr_count($html, 'role="radio"'));
        $this->assertSame(1, substr_count($html, 'aria-checked="true"'));
        $this->assertMatchesRegularExpression('/aria-checked="true"[^>]*"preset":"more"/s', $html, 'the current choice is checked');
        $this->assertMatchesRegularExpression('~"preset":"default"[^>]*>\s*<i[^>]*aria-hidden="true"></i>\s*<span>'.preg_quote(e(__('reminders.preset_default')), '~').'<span class="lt-reminder-default"> · '.preg_quote(e(__('reminders.picker_your_default')), '~').'</span></span>~', $html, '"Your default" is part of the item\'s name');
        $this->assertMatchesRegularExpression('~</div>\s*<a [^>]*role="button"[^>]*"forAllMyTodos":"1"~s', $html, '"Use this for all my to-dos" is a command outside the radio group');
        $this->assertStringContainsString('"forAllMyTodos":"1"', $html);
        $this->assertStringContainsString('hx-post="'.BASE_URL.'/hx/reminders/taskReminderMenu/choose"', $html);
        $this->assertStringContainsString('hx-target="#taskReminderMenu-5-abc123"', $html);
        $this->assertStringContainsString('"instance":"abc123"', $html, 'a save redraws the same menu');
        $this->assertStringNotContainsString('aria-describedby', $html, 'nothing to explain');
    }

    public function test_the_top_level_is_one_item_that_drills_into_the_choices(): void
    {
        $html = $this->render($this->state);

        $this->assertMatchesRegularExpression('~<li id="taskReminderMenu-5-abc123" class="lt-reminder-menu"[^>]*onclick="event.stopPropagation\(\);"~', $html, 'clicks inside keep the menu open');
        $this->assertStringContainsString('hx-trigger="lt:reminders:reminder.updated#5 from:body, lt:reminders:defaults.updated from:body"', $html, 'other menus for the to-do redraw after a change');
        $this->assertMatchesRegularExpression('~<a href="javascript:void\(0\);"\s+class="lt-reminder-choice lt-reminder-top"\s+role="button"\s+data-lt-drill-open\s+aria-expanded="false"\s+aria-controls="taskReminderMenu-5-abc123-panel">~', $html, 'a disclosure for the in-place panel');
        $this->assertStringNotContainsString('aria-haspopup', $html, 'nothing pops up: the menu\'s own content changes');
        $this->assertStringContainsString('<span class="lt-reminder-label">'.e(sprintf(__('reminders.remind_me_current'), __('reminders.short_more'))).'</span>', $html);
        $this->assertStringContainsString('<div id="taskReminderMenu-5-abc123-panel" class="lt-reminder-panel" hidden>', $html, 'the choices start hidden');
        $this->assertMatchesRegularExpression('~<button type="button" class="lt-reminder-choice lt-reminder-back" data-lt-drill-back aria-label="'.preg_quote(e(__('reminders.back')), '~').'">\s*<i[^>]*></i>\s*<span id="taskReminderMenu-5-abc123-label">'.preg_quote(e(__('reminders.remind_me')), '~').'</span>~', $html, 'Back, labelled "Remind me" for the group');
        $panel = substr($html, strpos($html, '-panel" class="lt-reminder-panel"'));
        $this->assertSame(4, substr_count($panel, 'role="radio"'), 'the choices are inside the panel');
    }

    public function test_the_top_label_names_the_current_choice(): void
    {
        foreach (['default', 'more', 'less', 'off'] as $preset) {
            $html = $this->render(['preset' => $preset] + $this->state);
            $this->assertStringContainsString(e(sprintf(__('reminders.remind_me_current'), __('reminders.short_'.$preset))), $html);
        }
        $this->assertStringContainsString(e(sprintf(__('reminders.remind_me_current'), __('reminders.short_off'))), $this->render(['preset' => 'off', 'chosen' => false, 'inherited' => 'off', 'isYours' => false] + $this->state), 'someone else\'s to-do starts at Off');
    }

    public function test_the_mount_shows_remind_me_while_it_loads(): void
    {
        $html = view('reminders::partials.taskReminderMenuMount', ['ticketId' => 9])->render();

        $this->assertMatchesRegularExpression('/<li id="taskReminderMenu-9-([a-f0-9]{8})" class="lt-reminder-menu"\s+hx-get="[^"]*ticketId=9&amp;instance=\1"\s+hx-trigger="intersect once"/', $html);
        $this->assertStringContainsString(e(__('reminders.remind_me')), $html);
        $this->assertStringNotContainsString('nav-header', $html, 'one item at the top level, no heading');
    }

    public function test_without_a_due_date_there_is_only_the_explanation(): void
    {
        $html = $this->render(['hasDueDate' => false] + $this->state);

        $this->assertStringContainsString('<span class="lt-reminder-label">'.e(__('reminders.remind_me')).'</span>', $html, 'just "Remind me" at the top level');
        $this->assertStringContainsString('<p id="taskReminderMenu-5-abc123-hint" class="lt-reminder-hint">'.e(__('reminders.picker_needs_due_date')).'</p>', $html);
        $this->assertStringNotContainsString('hx-post', $html);
        $this->assertStringNotContainsString('role="radio"', $html);
    }

    public function test_on_someone_else_s_to_do_off_is_the_default_and_the_explanation_describes_the_group(): void
    {
        $html = $this->render(['preset' => 'off', 'chosen' => false, 'inherited' => 'off', 'isYours' => false] + $this->state);

        $this->assertMatchesRegularExpression('/aria-checked="true"[^>]*"preset":"off"/s', $html);
        $this->assertStringContainsString('aria-describedby="taskReminderMenu-5-abc123-hint"', $html);
        $this->assertStringContainsString(e(__('reminders.picker_not_yours')), $html);
        $this->assertDoesNotMatchRegularExpression('/role="radio"[^>]*>[^<]*'.preg_quote(e(__('reminders.picker_not_yours')), '/').'/', $html, 'the explanation is not an item');

        $html = $this->render(['preset' => 'more', 'chosen' => true, 'inherited' => 'off', 'isYours' => false] + $this->state);
        $this->assertStringNotContainsString('forAllMyTodos', $html, 'only for your own to-dos');
    }

    public function test_the_partial_skips_use_for_all_when_the_to_do_follows_your_default(): void
    {
        $html = $this->render(['preset' => 'default', 'chosen' => false] + $this->state);

        $this->assertStringNotContainsString('forAllMyTodos', $html);
    }

    public function test_a_save_is_announced(): void
    {
        $html = $this->render($this->state, ['announce' => 'Saved. This to-do: Every morning until it\'s due.']);

        $this->assertStringContainsString('data-lt-announce="Saved. This to-do: Every morning until it&#039;s due."', $html);
    }

    public function test_the_instance_id_is_kept_and_only_ever_hex(): void
    {
        $this->controller('GET', ['ticketId' => 1, 'instance' => 'abc123'])->get();
        $this->assertSame('abc123', $this->assigned['instance']);

        $this->controller('GET', ['ticketId' => 1, 'instance' => '"><script>'])->get();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}$/', $this->assigned['instance'], 'anything else is replaced');
    }

    public function test_a_save_sets_what_is_announced(): void
    {
        $this->controller('POST', ['ticketId' => '1', 'preset' => 'less', 'instance' => 'abc123'])->choose();
        $this->assertSame('reminders.saved_choice', $this->assigned['announce']);
        $this->assertSame('abc123', $this->assigned['instance']);

        $this->controller('POST', ['ticketId' => '1', 'preset' => 'loud'])->choose();
        $this->assertSame('reminders.picker_not_saved', $this->assigned['announce']);
    }
}
