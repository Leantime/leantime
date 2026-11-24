<?php

namespace Unit\app\Domain\Tickets\Templates;

use Illuminate\Contracts\Auth\Access\Gate;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Plugins\Plugins;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Unit\TestCase;

/**
 * The milestone dialog lets a milestone depend on several milestones (#268), preselects the saved
 * ones and leaves out the milestones that would make the dependency circular.
 */
class MilestoneDialogRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        // The view factory discovers plugin composer paths and can() checks the permission
        // engine; both hit the database, which unit tests do not have.
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(Plugins::class, $plugins);

        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('currentUserCan')->willReturn(true);
        app()->instance(PermissionService::class, $permissions);

        // @can() goes through the gate, which would look the permission up in the database.
        $gate = $this->createMock(Gate::class);
        $gate->method('check')->willReturn(true);
        app()->instance(Gate::class, $gate);
    }

    /**
     * The variables the milestone controller assigns.
     *
     * @param  array<int, TicketModel>  $milestones
     * @param  array<int, int>  $milestoneDependents
     * @return array<string, mixed>
     */
    private function viewData(TicketModel $current, array $milestones, array $milestoneDependents): array
    {
        return [
            'milestone' => $current,
            'milestones' => $milestones,
            'milestoneDependents' => $milestoneDependents,
            'allAssignedprojects' => [],
            'statusLabels' => [],
            'users' => [],
            'comments' => [],
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

    private function milestone(int $id, string $headline, array $dependencies = []): TicketModel
    {
        $milestone = new TicketModel;
        $milestone->id = $id;
        $milestone->headline = $headline;
        $milestone->projectId = 7;
        $milestone->milestoneDependencies = $dependencies;

        return $milestone;
    }

    /**
     * @return array<int, string> The dependency <option> tags of the rendered dialog.
     */
    private function dependencyOptions(string $html): array
    {
        preg_match('/<select[^>]*name="dependentMilestone\[\]".*?<\/select>/s', $html, $select);
        preg_match_all('/<option[^>]*>/', $select[0] ?? '', $options);

        return $options[0];
    }

    public function test_the_dependency_field_is_a_multi_select_with_the_saved_dependencies_selected(): void
    {
        $current = $this->milestone(9, 'Launch', [11, 12]);

        $html = view('tickets::milestoneDialog', $this->viewData(
            $current,
            [$current, $this->milestone(11, 'Design'), $this->milestone(12, 'Build'), $this->milestone(13, 'Test')],
            []
        ))->render();

        $this->assertMatchesRegularExpression('/<select[^>]*name="dependentMilestone\[\]"[^>]*multiple/', $html);
        $this->assertStringContainsString('<input type="hidden" name="dependentMilestone[]" value="" />', $html);

        $options = $this->dependencyOptions($html);
        $this->assertCount(3, $options, 'the milestone itself is not offered');
        $this->assertStringContainsString('selected', $options[0]);
        $this->assertStringContainsString('selected', $options[1]);
        $this->assertStringNotContainsString('selected', $options[2]);
    }

    public function test_milestones_depending_on_this_one_are_not_offered(): void
    {
        $current = $this->milestone(9, 'Launch');

        $html = view('tickets::milestoneDialog', $this->viewData(
            $current,
            [$current, $this->milestone(20, 'Party', [9]), $this->milestone(13, 'Test')],
            [20]
        ))->render();

        $options = $this->dependencyOptions($html);
        $this->assertCount(1, $options);
        $this->assertStringContainsString('value="13"', $options[0]);
    }
}
