<?php

namespace Tests\Unit\app\Core\Modules;

use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Core\Modules\Module;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Core\Modules\ProjectModules;

/**
 * Per-project module switches: defaults, stored overrides, and the two questions the gates ask
 * (may this permission be granted here, is this route switched off here).
 */
class ProjectModulesTest extends \Unit\TestCase
{
    private ModuleRegistry $registry;

    /** In-memory stand-in for the zp_projects.metadata column. */
    private StoresProjectModules $store;

    private ProjectModules $projectModules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new ModuleRegistry;
        $this->registry->register(new Module(id: 'wiki', title: 'Docs', routes: ['wiki'], permissionDomain: 'wiki'));
        $this->registry->register(new Module(id: 'files', title: 'Files', routes: ['files.browse']));
        $this->registry->register(new Module(id: 'Whiteboardscanvas', title: 'Whiteboards', routes: ['Whiteboardscanvas'], source: 'Whiteboardscanvas'));
        $this->registry->register(new Module(id: 'beta', title: 'Beta', enabledByDefault: false, routes: ['beta']));
        $this->registry->register(new Module(id: 'boards', title: 'Boards', routes: ['boards', 'api.boards']));
        $this->registry->register(new Module(id: 'library', title: 'Library', routes: ['library.showAll']));

        $this->store = new class implements StoresProjectModules
        {
            /** @var array<int, array<string, bool>> */
            public array $rows = [];

            public int $reads = 0;

            public function getModuleOverrides(int $projectId): array
            {
                $this->reads++;

                return $this->rows[$projectId] ?? [];
            }

            public int $bulkReads = 0;

            public function getModuleOverridesForProjects(array $projectIds): array
            {
                $this->bulkReads++;
                $overridesByProject = [];

                foreach ($projectIds as $projectId) {
                    $overridesByProject[$projectId] = $this->rows[$projectId] ?? [];
                }

                return $overridesByProject;
            }

            public int $allReads = 0;

            public function getAllModuleOverrides(): array
            {
                $this->allReads++;

                return array_filter($this->rows);
            }

            public function updateModuleOverrides(int $projectId, callable $change): void
            {
                $this->rows[$projectId] = $change($this->rows[$projectId] ?? []);
            }
        };

        $this->projectModules = new ProjectModules($this->registry, $this->store);
    }

    public function test_switched_off_project_ids_come_from_one_read_and_refresh_after_a_save(): void
    {
        $this->store->rows = [
            7 => ['wiki' => false],
            8 => ['wiki' => true, 'files' => false],
            9 => ['files' => false],
        ];

        $this->assertSame([7], $this->projectModules->switchedOffProjectIds('wiki'));
        $this->assertSame([8, 9], $this->projectModules->switchedOffProjectIds('files'));
        $this->assertSame([], $this->projectModules->switchedOffProjectIds('unknown'));
        $this->assertSame(1, $this->store->allReads);

        $this->projectModules->save(9, ['wiki' => false]);

        $this->assertSame([7, 9], $this->projectModules->switchedOffProjectIds('wiki'));
        $this->assertSame(2, $this->store->allReads);
    }

    public function test_modules_follow_their_default_until_a_project_chooses(): void
    {
        $this->assertTrue($this->projectModules->isEnabled('wiki', 7));
        $this->assertFalse($this->projectModules->isEnabled('beta', 7));
    }

    public function test_unknown_modules_and_requests_without_a_project_count_as_on(): void
    {
        $this->store->rows[7] = ['wiki' => false];

        $this->assertTrue($this->projectModules->isEnabled('tickets', 7));
        $this->assertTrue($this->projectModules->isEnabled('wiki', null));
        $this->assertTrue($this->projectModules->isEnabled('wiki', 0));
    }

    public function test_saving_stores_only_choices_that_differ_from_the_default(): void
    {
        $this->projectModules->save(7, ['wiki' => false, 'files' => true, 'beta' => true]);

        $this->assertSame(['wiki' => false, 'beta' => true], $this->store->rows[7]);
        $this->assertFalse($this->projectModules->isEnabled('wiki', 7));
        $this->assertTrue($this->projectModules->isEnabled('beta', 7));

        $this->projectModules->save(7, ['wiki' => true]);

        $this->assertSame(['beta' => true], $this->store->rows[7]);
        $this->assertTrue($this->projectModules->isEnabled('wiki', 7));
    }

    public function test_saving_keeps_choices_for_modules_that_are_not_registered_right_now(): void
    {
        // A plugin that is disabled for the install isn't registered; its choice must survive.
        $this->store->rows[7] = ['pomodoro' => false];

        $this->projectModules->save(7, ['wiki' => false, 'pomodoro' => true]);

        $this->assertSame(['pomodoro' => false, 'wiki' => false], $this->store->rows[7]);
    }

    public function test_stored_choices_are_read_once_per_project(): void
    {
        $this->projectModules->isEnabled('wiki', 7);
        $this->projectModules->isEnabled('files', 7);
        $this->projectModules->isEnabled('wiki', 8);

        $this->assertSame(2, $this->store->reads);
    }

    public function test_module_ids_match_in_any_letter_case(): void
    {
        $this->projectModules->save(7, ['whiteboardscanvas' => false]);

        $this->assertSame(['Whiteboardscanvas' => false], $this->store->rows[7]);
        $this->assertFalse($this->projectModules->isEnabled('Whiteboardscanvas', 7));
        $this->assertFalse($this->projectModules->isEnabled('whiteboardscanvas', 7));
    }

    public function test_permissions_of_a_switched_off_module_are_not_allowed_in_that_project(): void
    {
        $this->store->rows[7] = ['wiki' => false];

        $this->assertFalse($this->projectModules->allowsPermission('wiki.view', 7));
        $this->assertTrue($this->projectModules->allowsPermission('wiki.view', 8));
        $this->assertTrue($this->projectModules->allowsPermission('wiki.view', null));
        $this->assertTrue($this->projectModules->allowsPermission('tickets.view', 7));
    }

    public function test_modules_without_a_permission_domain_never_deny_permissions(): void
    {
        // Files backs ticket attachments: switching the file library off must not touch files.*.
        $this->store->rows[7] = ['files' => false];

        $this->assertTrue($this->projectModules->allowsPermission('files.view', 7));
        $this->assertTrue($this->projectModules->allowsPermission('files.upload', 7));
    }

    public function test_routes_of_a_switched_off_module_are_blocked(): void
    {
        $this->store->rows[7] = ['wiki' => false, 'files' => false, 'Whiteboardscanvas' => false, 'library' => false];

        $this->assertSame('wiki', $this->projectModules->blockingRoute('wiki', 'show', 7)?->id);
        $this->assertSame('files', $this->projectModules->blockingRoute('files', 'browse', 7)?->id);
        $this->assertSame('Whiteboardscanvas', $this->projectModules->blockingRoute('whiteboardscanvas', 'showCanvas', 7)?->id);

        // Matched like the Frontcontroller resolves controllers: these all reach the ShowAll action.
        $this->assertSame('library', $this->projectModules->blockingRoute('library', 'show-all', 7)?->id);
        $this->assertSame('library', $this->projectModules->blockingRoute('library', 'show_all', 7)?->id);
        $this->assertSame('library', $this->projectModules->blockingRoute('Library', 'ShowAll', 7)?->id);
        $this->assertSame('library', $this->projectModules->blockingRoute('library', 'show all', 7)?->id);

        // Only the claimed actions of Files are blocked; attachments still upload, download, and
        // are listed and deleted through the to-do modal's files/showAll.
        $this->assertNull($this->projectModules->blockingRoute('files', 'upload', 7));
        $this->assertNull($this->projectModules->blockingRoute('files', 'get', 7));
        $this->assertNull($this->projectModules->blockingRoute('files', 'showAll', 7));

        $this->assertNull($this->projectModules->blockingRoute('wiki', 'show', 8));
        $this->assertNull($this->projectModules->blockingRoute('tickets', 'showKanban', 7));
    }

    public function test_native_api_routes_can_be_claimed(): void
    {
        // e.g. PATCH /api/blueprints/{slug}: module "api", action "blueprints".
        $this->store->rows[7] = ['boards' => false];

        $this->assertSame('boards', $this->projectModules->blockingRoute('api', 'boards', 7)?->id);
        $this->assertNull($this->projectModules->blockingRoute('api', 'canvas', 7));
    }

    public function test_filtering_rows_reads_all_their_projects_at_once(): void
    {
        $this->store->rows[5] = ['wiki' => false];

        $rows = [
            ['id' => 1, 'projectId' => 5],
            ['id' => 2, 'projectId' => 6],
            ['id' => 3, 'projectId' => 7],
            ['id' => 4, 'projectId' => 6],
        ];

        $this->assertSame([2, 3, 4], array_column($this->projectModules->withoutSwitchedOffProjects('wiki', $rows), 'id'));
        $this->assertSame(1, $this->store->bulkReads);
        $this->assertSame(0, $this->store->reads, 'no per-project reads');

        // A module that isn't registered filters nothing and reads nothing.
        $this->assertCount(4, $this->projectModules->withoutSwitchedOffProjects('pomodoro', $rows));
        $this->assertSame(1, $this->store->bulkReads);
    }

    public function test_copy_gives_the_new_project_the_same_choices(): void
    {
        $this->store->rows[7] = ['wiki' => false, 'beta' => true];

        $this->projectModules->copy(7, 9);

        $this->assertFalse($this->projectModules->isEnabled('wiki', 9));
        $this->assertTrue($this->projectModules->isEnabled('beta', 9));
    }

    public function test_states_list_every_registered_module(): void
    {
        $this->store->rows[7] = ['wiki' => false];

        $this->assertSame(
            ['wiki' => false, 'files' => true, 'Whiteboardscanvas' => true, 'beta' => false, 'boards' => true, 'library' => true],
            $this->projectModules->statesFor(7)
        );
    }
}
