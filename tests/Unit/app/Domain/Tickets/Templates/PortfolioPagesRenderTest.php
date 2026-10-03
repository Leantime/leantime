<?php

namespace Unit\app\Domain\Tickets\Templates;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Plugins\Plugins;
use Leantime\Core\UI\Template;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Tickets\Controllers\ShowAllMilestonesOverview;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * /tickets/roadmapAll and /tickets/showAllMilestonesOverview both include
 * tickets::submodules.portfolioTabs. That partial was deleted in 2023, so both
 * pages 500'd with "View not found" (#3819). Once the tabs were back the
 * milestones overview still 500'd on the shared ticket filter, because its
 * controller never assigned the group-by/sort option lists.
 *
 * These render both pages with the data their controllers assign so a missing
 * partial or variable fails here instead of in production.
 */
class PortfolioPagesRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        // The view factory discovers plugin composer paths and can() checks the
        // permission engine; both hit the database, which unit tests do not have.
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(Plugins::class, $plugins);

        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('currentUserCan')->willReturn(true);
        app()->instance(PermissionService::class, $permissions);
    }

    public function test_roadmap_all_renders_with_portfolio_tabs(): void
    {
        $html = view('tickets::roadmapAll', $this->sharedData() + [
            'milestones' => [(object) [
                'id' => 5,
                'headline' => 'Launch',
                'type' => 'milestone',
                'projectName' => 'Project A',
                'editFrom' => '2026-01-01 00:00:00',
                'editTo' => '2026-02-01 00:00:00',
                'editorId' => 1,
                'milestoneid' => 0,
                'dependingTicketId' => 0,
                'percentDone' => 10,
                'sortIndex' => 1,
                'tags' => '#1b75bb',
            ]],
            'clients' => [['id' => 1, 'name' => 'Client A']],
            'currentClientName' => '',
            'currentClient' => 0,
        ])->render();

        $this->assertStringContainsString('/tickets/showAllMilestonesOverview', $html);
        $this->assertStringContainsString('/projects/showMy', $html);
    }

    /**
     * Drives the real ShowAllMilestonesOverview controller (services mocked) and
     * renders exactly what it assigns, so dropping an assignment the template or
     * the shared ticket filter needs fails here instead of 500ing in production.
     */
    public function test_milestones_overview_controller_renders_with_ticket_filter(): void
    {
        if (! defined('CURRENT_URL')) {
            define('CURRENT_URL', 'http://localhost/tickets/showAllMilestonesOverview');
        }
        session(['userdata.id' => 1]);

        $tickets = $this->createMock(TicketService::class);
        $tickets->method('getMilestonesOverviewSearchCriteria')->willReturn([
            'groupBy' => '', 'users' => '', 'status' => 'not_done', 'term' => '',
            'milestone' => '', 'type' => '', 'priority' => '',
        ]);
        $tickets->method('getAllMilestonesOverview')->willReturn([(object) [
            'id' => 5,
            'headline' => 'Launch',
            'projectName' => 'Project A',
            'status' => 3,
            'milestoneid' => 0,
            'milestoneHeadline' => '',
            'milestoneColor' => '#1b75bb',
            'editorId' => 1,
            'editorFirstname' => 'Ada',
            'editFrom' => '2026-01-01 00:00:00',
            'editTo' => '2026-02-01 00:00:00',
            'percentDone' => 10,
            'planHours' => 2,
            'hourRemaining' => 1,
            'bookedHours' => 1,
        ]]);
        $tickets->method('getStatusLabels')->willReturn([
            3 => ['name' => 'New', 'class' => 'label-info', 'statusType' => 'NEW'],
            0 => ['name' => 'Done', 'class' => 'label-success', 'statusType' => 'DONE'],
        ]);
        $tickets->method('getTicketTypes')->willReturn(['task']);
        $tickets->method('getGroupByFieldOptions')->willReturn([['id' => 'all', 'field' => 'all', 'class' => '', 'label' => 'no_group']]);
        $tickets->method('getSortByFieldOptions')->willReturn([['id' => 'date', 'field' => 'date', 'class' => '', 'label' => 'date']]);
        app()->instance(TicketService::class, $tickets);
        $users = $this->createMock(UserService::class);
        $users->method('getAll')->willReturn([]);
        app()->instance(UserService::class, $users);
        app()->instance(ClientService::class, $this->createMock(ClientService::class));

        // Capture what the controller assigns and render the page from exactly that.
        $assigned = [];
        $tpl = $this->createMock(Template::class);
        $tpl->method('assign')->willReturnCallback(function (string $name, mixed $value) use (&$assigned): void {
            $assigned[$name] = $value;
        });
        $tpl->method('display')->willReturnCallback(function (string $template) use (&$assigned): Response {
            $view = 'tickets::'.substr($template, strlen('tickets.'));

            return new Response(view($view, $this->sharedData() + $assigned)->render());
        });

        $controller = new ShowAllMilestonesOverview(
            $this->createMock(IncomingRequest::class),
            $tpl,
            $this->createMock(Language::class),
        );
        $html = (string) $controller->get([])->getContent();

        $this->assertStringContainsString('/tickets/roadmapAll', $html);
        $this->assertStringContainsString('allTicketsTable', $html);
        // initMilestoneTable() binds DataTables + Buttons to .ticketTable; without the
        // class it ran on an empty set and threw "reading '_buttons'".
        $this->assertMatchesRegularExpression('/<table id="allTicketsTable" class="[^"]*\\bticketTable\\b/', $html);
    }

    /**
     * Stand-ins for the layout and the globals Template::setupGlobalVars() shares.
     */
    private function sharedData(): array
    {
        $layoutDir = sys_get_temp_dir().'/lt-portfolio-layout-'.getmypid();
        if (! is_dir($layoutDir)) {
            mkdir($layoutDir);
        }
        file_put_contents($layoutDir.'/stub.blade.php', '@yield("content") @stack("scripts")');
        app('view')->addNamespace('portfoliotest', $layoutDir);

        return [
            'layout' => 'portfoliotest::stub',
            'tpl' => new class
            {
                public function __call(string $name, array $arguments): string
                {
                    return '';
                }
            },
            'login' => new class
            {
                public static function userIsAtLeast(string $role): bool
                {
                    return true;
                }
            },
            'roles' => Roles::class,
        ];
    }
}
