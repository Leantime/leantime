<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Reminders\Controllers\ThisWeek;
use Leantime\Domain\Reminders\Services\ComingUpList;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * The "Your week" panel the weekly summary links to: This week (rows, then the routine, planned
 * and "still open" lines), Last week (a count and the named to-dos), then plugin sections, and a
 * footer naming the person's day and time.
 */
class ThisWeekPanelTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function language(): Language
    {
        $strings = parse_ini_file(APP_ROOT.'/app/Language/en-US.ini', false, INI_SCANNER_RAW);

        return $this->make(Language::class, ['__' => fn (string $key) => $strings[$key] ?? $key]);
    }

    /** Monday 23 November 2026, UTC. */
    private function week(): array
    {
        $item = fn (string $kind, int $id, int $days, string $due) => [
            'kind' => $kind, 'id' => $id, 'title' => ucfirst($kind).' '.$id, 'projectId' => 3, 'projectName' => 'Eastside pantry',
            'due' => $due, 'daysUntil' => $days, 'isYours' => true, 'url' => 'https://pm.example.test/dashboard/home#/tickets/showTicket/'.$id,
        ];

        return [
            'week' => ['start' => '2026-11-23', 'end' => '2026-11-29'],
            'today' => null,
            'ahead' => [
                'kind' => 'ahead',
                'items' => [$item('task', 1, 0, '2026-11-23T17:00:00Z'), $item('milestone', 2, 4, '2026-11-27T17:00:00Z')],
                'moreCount' => 0, 'dueCount' => 1, 'endingCount' => 1,
                'planned' => [['date' => '2026-11-25', 'count' => 2, 'timeBoxed' => 1]],
                'plannedCount' => 2,
                'routine' => ['count' => 1],
                'overdueCount' => 3,
            ],
            'lookBack' => [
                'kind' => 'lookBack', 'start' => '2026-11-16', 'end' => '2026-11-22', 'count' => 1,
                'items' => [['kind' => 'task', 'id' => 9, 'title' => 'Ship v2', 'projectId' => 3, 'projectName' => 'Eastside pantry', 'url' => 'https://pm.example.test/dashboard/home#/tickets/showTicket/9', 'isYours' => true, 'milestoneId' => null, 'finishedAt' => '2026-11-20T10:00:00Z', 'closedBy' => ['userId' => 8, 'name' => 'Marcel']]],
            ],
            'sections' => [
                ['kind' => 'ahead'],
                ['kind' => 'lookBack'],
                ['kind' => 'plugin:myweek.picks', 'title' => 'What you picked', 'lines' => ['Sooner: 2'], 'url' => 'https://pm.example.test/my-week', 'countsForSending' => true],
            ],
            'isEmpty' => false,
        ];
    }

    public function test_the_week_is_presented_in_sections_with_plain_lines(): void
    {
        $panel = (new ComingUpList($this->language()))->presentWeek($this->week(), 'UTC');

        $this->assertSame(['ahead', 'lookBack', 'plugin-myweek-picks'], array_column($panel['sections'], 'key'));

        [$ahead, $lookBack, $plugin] = $panel['sections'];
        $this->assertSame(['Due today · Eastside pantry', 'Milestone ends Friday · Eastside pantry'], array_column($ahead['items'], 'why'));
        $this->assertSame('#/tickets/showTicket/1', $ahead['items'][0]['href'], 'opens on the page the panel is on');
        $this->assertSame(['Routine tasks this week: 1', 'Wed: 2 planned (1 with a set time)', '3 still open from earlier'], $ahead['notes']);

        $this->assertSame('Last week', $lookBack['label']);
        $this->assertSame(['You finished 1 to-do last week.'], $lookBack['notes']);
        $this->assertSame('Eastside pantry · closed by Marcel', $lookBack['items'][0]['why']);
        $this->assertSame('done', $lookBack['items'][0]['kind']);

        $this->assertSame('What you picked', $plugin['label']);
        $this->assertSame(['Sooner: 2'], $plugin['notes']);
        $this->assertSame(['url' => 'https://pm.example.test/my-week', 'label' => 'What you picked'], $plugin['link']);
        $this->assertFalse($panel['isEmpty']);
    }

    public function test_an_empty_week_says_so(): void
    {
        $week = $this->week();
        $week['ahead'] = ['items' => [], 'planned' => [], 'routine' => ['count' => 0], 'overdueCount' => 0] + $week['ahead'];
        $week['lookBack'] = null;
        $week['sections'] = [['kind' => 'ahead']];

        $this->assertSame(['sections' => [], 'isEmpty' => true], (new ComingUpList($this->language()))->presentWeek($week, 'UTC'));
    }

    /** @return array<string, mixed> what the controller handed the template */
    private function openPanel(array $settings, ?\Closure $week = null): array
    {
        $assigned = [];
        $tpl = $this->make(Template::class, [
            'assign' => function ($name, $value) use (&$assigned) {
                $assigned[$name] = $value;
            },
            'displayPartial' => fn (string $template, int $responseCode = 200) => new Response($template, $responseCode),
        ]);
        $reminders = $this->make(Reminders::class, [
            'getSettings' => fn () => $settings + ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'UTC',
                'weekly' => true, 'weeklyDay' => 1, 'weeklyTime' => '08:00', 'weeklyLookBack' => true, 'workspaceEnabled' => true],
            'getWeeklySummary' => $week ?? function (bool $includeToday = false, int $limit = 5) use (&$assigned) {
                $assigned['__args'] = [$includeToday, $limit];

                return $this->week();
            },
        ]);

        $controller = (new \ReflectionClass(ThisWeek::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(ThisWeek::class, 'tpl'))->setValue($controller, $tpl);
        (new \ReflectionProperty(ThisWeek::class, 'language'))->setValue($controller, $this->language());
        $controller->init($reminders, new ComingUpList($this->language()), $this->make(ReminderSettings::class, [
            'timeFormat' => fn (int $userId) => 'h:i A',
        ]));
        $assigned['__response'] = $controller->get([]);

        return $assigned;
    }

    public function test_the_panel_asks_for_the_whole_week_and_names_the_day_and_time(): void
    {
        $assigned = $this->openPanel(['weeklyDay' => 5, 'weeklyTime' => '16:30']);

        $this->assertSame('reminders.thisWeek', $assigned['__response']->getContent());
        $this->assertSame([false, 0], $assigned['__args'], 'everything, without today folded in');
        $this->assertTrue($assigned['weeklyOn']);
        $this->assertSame('Friday', $assigned['weeklyDay']);
        $this->assertSame('04:30 PM', $assigned['weeklyTime']);

        $this->assertFalse($this->openPanel(['weekly' => false])['weeklyOn']);
        $this->assertFalse($this->openPanel(['workspaceEnabled' => false])['weeklyOn']);
    }

    public function test_only_for_a_signed_in_person(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->openPanel([], function () {
            throw new AuthorizationException;
        });
    }

    public function test_the_template_renders_rows_notes_and_the_footer(): void
    {
        $plugins = $this->createMock(\Leantime\Core\Plugins\Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(\Leantime\Core\Plugins\Plugins::class, $plugins);

        $panel = (new ComingUpList($this->language()))->presentWeek($this->week(), 'UTC');
        $html = view('reminders::thisWeek', ['panel' => $panel, 'weeklyOn' => true, 'weeklyDay' => 'Monday', 'weeklyTime' => '08:00'])->render();

        foreach (['This week', 'Task 1', 'Wed: 2 planned (1 with a set time)', 'Last week', 'Ship v2', 'closed by Marcel', 'What you picked', 'href="https://pm.example.test/my-week"', 'You get this summary on Monday at 08:00'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('fa-square-check', $html, 'finished to-dos get a done icon');

        $empty = view('reminders::thisWeek', ['panel' => ['sections' => [], 'isEmpty' => true], 'weeklyOn' => false, 'weeklyDay' => 'Monday', 'weeklyTime' => '08:00'])->render();
        $this->assertStringContainsString('Nothing due or planned in the next seven days.', $empty);
        $this->assertStringContainsString('Your weekly summary is off.', $empty);
    }
}
