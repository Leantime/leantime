<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Reminders\Controllers\ComingUp;
use Leantime\Domain\Reminders\Services\ComingUpList;
use Leantime\Domain\Reminders\Services\Reminders;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * The "Coming up" panel: the signed-in person's whole summary list in a dialog, grouped like the
 * email, with plain "why" lines, a reminders menu on each to-do, and a footer about the summary.
 */
class ComingUpPanelTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function language(): Language
    {
        $strings = parse_ini_file(APP_ROOT.'/app/Language/en-US.ini', false, INI_SCANNER_RAW);

        return $this->make(Language::class, ['__' => fn (string $key) => $strings[$key] ?? $key]);
    }

    private function item(string $kind, int $id, int $days, string $due, bool $isYours = true, string $url = ''): array
    {
        return [
            'kind' => $kind, 'id' => $id, 'title' => ucfirst($kind).' '.$id, 'projectId' => 3, 'projectName' => 'Eastside pantry',
            'due' => $due, 'daysUntil' => $days, 'isYours' => $isYours,
            'url' => $url !== '' ? $url : 'https://pm.example.test/dashboard/home#/tickets/showTicket/'.$id,
        ];
    }

    /** Sunday 22 November 2026 in New York. */
    private function summary(): array
    {
        return [
            'date' => '2026-11-22',
            'items' => [
                $this->item('task', 1, 0, '2026-11-22T22:00:00Z'),
                $this->item('milestone', 2, 1, '2026-11-23T17:00:00Z', url: 'https://pm.example.test/dashboard/home#/tickets/editMilestone/2'),
                $this->item('task', 3, 1, '2026-11-23T22:00:00Z', isYours: false),
                $this->item('project', 4, 5, '2026-11-27T17:00:00Z', url: 'https://pm.example.test/projects/changeCurrentProject/4'),
                $this->item('task', 5, 12, '2026-12-04T22:00:00Z'),
            ],
            'moreCount' => 0,
            'routine' => ['count' => 1, 'items' => []],
            'overdueCount' => 2,
            'dueToday' => 2, 'dueTomorrow' => 2, 'later' => 2, 'isEmpty' => false,
        ];
    }

    public function test_sections_follow_the_email_grouping_with_plain_why_lines(): void
    {
        $panel = (new ComingUpList($this->language()))->present($this->summary(), 'America/New_York');

        $this->assertSame(['Today', 'Tomorrow', 'Later this week', 'Later'], array_column($panel['sections'], 'label'));
        $why = fn (int $section, int $row) => $panel['sections'][$section]['items'][$row]['why'];
        $this->assertSame('Due today · Eastside pantry', $why(0, 0));
        $this->assertSame('Milestone ends tomorrow · Eastside pantry', $why(1, 0));
        $this->assertSame('Due tomorrow · You asked to be reminded', $why(1, 1), 'someone else\'s to-do you opted in to');
        $this->assertSame('Project ends Friday', $why(2, 0), 'within a week: the weekday, in the person\'s timezone');
        $this->assertSame('Due in 12 days · Eastside pantry', $why(3, 0));
        $this->assertSame('Routine tasks today: 1', $panel['sections'][0]['note']);
        $this->assertSame('2 still open from earlier', $panel['stillOpen']);
        $this->assertFalse($panel['isEmpty']);
    }

    public function test_task_and_milestone_links_open_their_dialog_on_the_same_page(): void
    {
        $panel = (new ComingUpList($this->language()))->present($this->summary(), 'UTC');

        $this->assertSame('#/tickets/showTicket/1', $panel['sections'][0]['items'][0]['href']);
        $this->assertSame('#/tickets/editMilestone/2', $panel['sections'][1]['items'][0]['href']);
        $this->assertSame('https://pm.example.test/projects/changeCurrentProject/4', $panel['sections'][2]['items'][0]['href']);
    }

    public function test_nothing_coming_up_is_empty_but_still_says_what_is_open(): void
    {
        $list = new ComingUpList($this->language());
        $empty = ['items' => [], 'routine' => ['count' => 0, 'items' => []], 'overdueCount' => 0];

        $this->assertTrue($list->present($empty, 'UTC')['isEmpty']);
        $this->assertSame([], $list->present($empty, 'UTC')['sections']);

        $panel = $list->present(['overdueCount' => 3] + $empty, 'UTC');
        $this->assertTrue($panel['isEmpty']);
        $this->assertSame('3 still open from earlier', $panel['stillOpen']);

        $routineOnly = $list->present(['routine' => ['count' => 2, 'items' => []]] + $empty, 'UTC');
        $this->assertFalse($routineOnly['isEmpty'], 'today\'s routine line is something');
    }

    /** @return array<string, mixed> what the controller handed the template */
    private function openPanel(array $settings, ?\Closure $summary = null, string $timeFormat = 'H:i'): array
    {
        $assigned = [];
        $tpl = $this->make(Template::class, [
            'assign' => function ($name, $value) use (&$assigned) {
                $assigned[$name] = $value;
            },
            'displayPartial' => fn (string $template, int $responseCode = 200) => new Response($template, $responseCode),
        ]);
        $reminders = $this->make(Reminders::class, [
            'getSettings' => fn () => $settings + ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'UTC', 'workspaceEnabled' => true],
            'getSummary' => $summary ?? function (int $limit = 5) use (&$assigned) {
                $assigned['__limit'] = $limit;

                return $this->summary();
            },
        ]);

        $controller = (new \ReflectionClass(ComingUp::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(ComingUp::class, 'tpl'))->setValue($controller, $tpl);
        $controller->init($reminders, new ComingUpList($this->language()), $this->make(\Leantime\Domain\Reminders\Services\ReminderSettings::class, [
            'timeFormat' => fn (int $userId) => $timeFormat,
        ]));
        $assigned['__response'] = $controller->get([]);

        return $assigned;
    }

    public function test_the_panel_asks_for_the_whole_list_and_names_the_summary_time(): void
    {
        $assigned = $this->openPanel(['summaryTime' => '07:30']);

        $this->assertSame('reminders.comingUp', $assigned['__response']->getContent());
        $this->assertSame(0, $assigned['__limit'], 'all of it, not the top 5');
        $this->assertTrue($assigned['summaryOn']);
        $this->assertSame('07:30', $assigned['summaryTime'], '24-hour format');
        $this->assertSame('02:00 PM', $this->openPanel(['summaryTime' => '14:00'], timeFormat: 'h:i A')['summaryTime'], '12-hour format');
        $this->assertCount(4, $assigned['panel']['sections']);
    }

    public function test_the_footer_says_when_the_summary_is_off_for_you_or_the_workspace(): void
    {
        $this->assertFalse($this->openPanel(['summary' => false])['summaryOn']);
        $this->assertFalse($this->openPanel(['workspaceEnabled' => false])['summaryOn']);
    }

    public function test_only_for_a_signed_in_person(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->openPanel([], function () {
            throw new AuthorizationException;
        });
    }

    private function render(array $panel, bool $summaryOn = true): string
    {
        $plugins = $this->createMock(\Leantime\Core\Plugins\Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(\Leantime\Core\Plugins\Plugins::class, $plugins);

        return view('reminders::comingUp', ['panel' => $panel, 'summaryOn' => $summaryOn, 'summaryTime' => '8:00'])->render();
    }

    public function test_rows_link_to_their_item_and_to_dos_carry_the_reminders_menu(): void
    {
        $html = $this->render((new ComingUpList($this->language()))->present($this->summary(), 'America/New_York'));

        $this->assertMatchesRegularExpression('~<h4 class="lt-modal-title" id="lt-modal-title-[a-f0-9]{8}">\s*'.preg_quote(e(__('reminders.panel_title')), '~').'\s*<\/h4>~', $html, 'a real heading with an id the dialog is labelled by');
        $this->assertStringContainsString(e(__('reminders.panel_intro')), $html);
        $this->assertStringContainsString('href="#/tickets/showTicket/1"', $html);
        $this->assertSame(3, substr_count($html, 'hx-get="'.BASE_URL.'/hx/reminders/taskReminderMenu/get?ticketId='), 'one reminders menu per to-do');
        $this->assertMatchesRegularExpression('/id="taskReminderMenu-3-[a-f0-9]{8}"/', $html);
        $this->assertDoesNotMatchRegularExpression('/taskReminderMenu-2-/', $html, 'milestones have no reminders menu');
        $this->assertStringContainsString('data-lt-dropdown-float', $html, 'the row menu floats over the dialog');
        $this->assertStringContainsString('data-lt-menu-keys', $html);
        $this->assertStringContainsString('aria-label="'.e(sprintf(__('reminders.panel_row_actions'), 'Task 1')).'"', $html, 'each row\'s ⋮ has its own name');
        $this->assertStringContainsString('fa-solid fa-diamond', $html);
        $this->assertStringContainsString('fa-regular fa-flag', $html);
        $this->assertStringContainsString(e('You get this summary at 8:00 when something is coming up.'), $html);
        $this->assertStringContainsString('href="'.BASE_URL.'/users/editOwn#notifications"', $html);
        $this->assertStringNotContainsString('overdue', strtolower(strip_tags($html)));
    }

    public function test_the_empty_and_off_states(): void
    {
        $html = $this->render(['sections' => [], 'stillOpen' => null, 'isEmpty' => true], summaryOn: false);

        $this->assertStringContainsString(e(__('reminders.panel_empty')), $html);
        $this->assertStringContainsString(e(__('reminders.panel_footer_off')), $html);
        $this->assertStringNotContainsString('taskReminderMenu', $html);
    }

    public function test_the_panel_is_not_in_the_avatar_menu(): void
    {
        $template = (string) file_get_contents(APP_ROOT.'/app/Domain/Auth/Templates/partials/loginInfo.blade.php');

        $this->assertStringNotContainsString('reminders/comingUp', $template, 'the bell row, the email and the push are the ways in');
    }

    public function test_times_are_formatted_from_storage(): void
    {
        $this->assertSame('14:00', \Leantime\Domain\Reminders\Services\ReminderSettings::formatTime('14:00', 'H:i'));
        $this->assertSame('02:00 PM', \Leantime\Domain\Reminders\Services\ReminderSettings::formatTime('14:00', 'h:i A'));
        $this->assertSame('8:02 am', \Leantime\Domain\Reminders\Services\ReminderSettings::formatTime('08:02', 'g:i a'));
        $this->assertSame('nonsense', \Leantime\Domain\Reminders\Services\ReminderSettings::formatTime('nonsense', 'H:i'), 'left as it is');
    }
}
