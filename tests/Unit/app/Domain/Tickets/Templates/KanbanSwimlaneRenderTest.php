<?php

namespace Unit\app\Domain\Tickets\Templates;

use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\SQLiteConnection;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Plugins\Plugins;
use Leantime\Core\UI\UiState;
use PDO;
use Unit\TestCase;

class KanbanSwimlaneRenderTest extends TestCase
{
    private SQLiteConnection $db;

    protected function setUp(): void
    {
        parent::setUp();
        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(Plugins::class, $plugins);
        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('currentUserCan')->willReturn(false);
        app()->instance(PermissionService::class, $permissions);
        session(['userdata.id' => 7]);
        app()->instance(Gate::class, app(Gate::class)->forUser((object) ['id' => 7]));

        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $this->db->getSchemaBuilder()->create('zp_settings', function ($table) {
            $table->string('key', 175)->primary();
            $table->text('value')->nullable();
        });
    }

    public function test_saved_collapse_survives_a_new_request_and_can_be_reopened(): void
    {
        (new UiState($this->db))->set('swimlane_launch', 'closed');
        $html = $this->renderBoard();
        $this->assertStringContainsString('data-expanded="false" id="swimlane-row-launch"', $html);
        $this->assertStringContainsString('kanban-swimlane-content collapsed', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);

        (new UiState($this->db))->set('swimlane_launch', 'open');
        $html = $this->renderBoard();
        $this->assertStringContainsString('data-expanded="true" id="swimlane-row-launch"', $html);
        $this->assertStringNotContainsString('kanban-swimlane-content collapsed', $html);
    }

    public function test_new_user_does_not_inherit_another_users_collapse(): void
    {
        (new UiState($this->db))->set('swimlane_launch', 'closed');
        session(['userdata.id' => 8]);
        $this->assertStringContainsString('data-expanded="true" id="swimlane-row-launch"', $this->renderBoard());
    }

    /** @dataProvider alerts */
    public function test_board_renders_the_breakdown_alert(string $alert, string $label): void
    {
        $this->assertStringContainsString($label, $this->renderBoard($alert));
    }

    public static function alerts(): array
    {
        return [
            ['overdue', 'Overdue - Past due date'],
            ['dueSoon', 'Due Soon - Within 3 days'],
            ['stale', 'Stale - No activity for 14+ days'],
        ];
    }

    public function test_a_lane_without_an_alert_does_not_render_an_indicator(): void
    {
        $dom = new DOMDocument;
        @$dom->loadHTML($this->renderBoard());
        $this->assertSame(0, (new DOMXPath($dom))->query('//span[contains(@class, "swimlane-time-indicator")]')->length);
    }

    private function renderBoard(?string $alert = null): string
    {
        // Exercise the actual board and components; unrelated navigation/layout is a stand-in.
        $dir = sys_get_temp_dir().'/lt-swimlane-render-'.getmypid();
        if (! is_dir($dir.'/submodules')) {
            mkdir($dir.'/submodules', 0777, true);
        }
        file_put_contents($dir.'/layout.blade.php', '@yield("content")');
        file_put_contents($dir.'/submodules/ticketHeader.blade.php', '');
        file_put_contents($dir.'/submodules/ticketBoardTabs.blade.php', '');
        app('view')->addNamespace('swimlanetest', $dir);
        app('view')->prependNamespace('tickets', $dir);
        app()->instance(UiState::class, new UiState($this->db));
        $globals = ['tpl' => new class
        {
            public function __call(string $name, array $arguments): string
            {
                return '';
            }
        }];
        app('view')->share($globals);

        return view('tickets::showKanban', $globals + [
            'layout' => 'swimlanetest::layout',
            'ticketTypeIcons' => [],
            'allTickets' => [['id' => 'launch', 'label' => 'Launch', 'items' => []]],
            'allKanbanColumns' => [1 => ['name' => 'New', 'class' => 'label-info']],
            'searchCriteria' => ['groupBy' => 'milestoneid'],
            'statusBreakdown' => ['launch' => ['statusCounts' => [1 => 0], 'totalCount' => 0, 'timeAlert' => $alert]],
        ])->render();
    }
}
