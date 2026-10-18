<?php

namespace Unit\app\Domain\Search;

use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Core\Modules\Module;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Core\Modules\ProjectModules;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Services\Search;

/**
 * Search leaves out projects that switched a provider's module off (Project settings -> Modules),
 * in the query itself so limits and paging stay right, and won't open a result there.
 */
class SearchModulesTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, SearchProvider> */
    private array $providers = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 1, 'role' => Roles::$admin]]);

        $this->providers = [
            'wiki' => $this->provider('wiki'),
            'goals' => $this->provider('goals'),
            'files' => $this->provider('files'),
            'tickets' => $this->provider('tickets'),
        ];
    }

    private function provider(string $key): SearchProvider
    {
        return new class($key) implements SearchProvider
        {
            public ?SearchQuery $lastQuery = null;

            public function __construct(private string $providerKey) {}

            public function key(): string
            {
                return $this->providerKey;
            }

            public function label(): string
            {
                return $this->providerKey;
            }

            public function icon(): string
            {
                return '';
            }

            public function available(): bool
            {
                return true;
            }

            public function search(SearchQuery $query): array
            {
                $this->lastQuery = $query;

                return [];
            }

            public function resolveTarget(int $id): ?SearchTarget
            {
                return new SearchTarget(url: '/'.$this->providerKey.'/'.$id, projectId: $id);
            }
        };
    }

    /** Projects 5 and 6 have Docs off, 6 also has Goals and Files off. */
    private function search(): Search
    {
        $registry = new ModuleRegistry;
        $registry->register(new Module(id: 'wiki', title: 'Docs', routes: ['wiki'], permissionDomain: 'wiki'));
        $registry->register(new Module(id: 'goalcanvas', title: 'Goals', routes: ['goalcanvas'], permissionDomain: 'goals'));
        $registry->register(new Module(id: 'files', title: 'Files', routes: ['files.browse']));

        $overrides = [
            5 => ['wiki' => false],
            6 => ['wiki' => false, 'goalcanvas' => false, 'files' => false],
        ];

        $store = $this->makeEmpty(StoresProjectModules::class, [
            'getAllModuleOverrides' => fn (): array => $overrides,
            'getModuleOverrides' => fn (int $projectId): array => $overrides[$projectId] ?? [],
        ]);

        $search = new Search($this->makeEmpty(ProjectService::class), new ProjectModules($registry, $store));

        $providersProperty = new \ReflectionProperty(Search::class, 'providers');
        $providersProperty->setValue($search, $this->providers);

        return $search;
    }

    public function test_each_type_leaves_out_projects_with_its_module_switched_off(): void
    {
        $this->search()->quickSearch('roadmap');

        $this->assertSame([5, 6], $this->providers['wiki']->lastQuery->excludedProjectIds);
        $this->assertSame([6], $this->providers['goals']->lastQuery->excludedProjectIds, 'goals follow the goalcanvas module');
        $this->assertSame([], $this->providers['tickets']->lastQuery->excludedProjectIds, 'tickets are no module');
        $this->assertSame([], $this->providers['files']->lastQuery->excludedProjectIds, 'Files only hides the library');
    }

    public function test_the_results_page_excludes_them_too(): void
    {
        $this->search()->search('roadmap', 'wiki', 20, 20);

        $query = $this->providers['wiki']->lastQuery;
        $this->assertSame([5, 6], $query->excludedProjectIds);
        $this->assertSame(20, $query->offset);
    }

    public function test_a_result_in_a_switched_off_project_does_not_open(): void
    {
        $search = $this->search();

        $this->assertNull($search->resolveTarget('wiki', 5));
        $this->assertSame('/wiki/7', $search->resolveTarget('wiki', 7)?->url);
        $this->assertSame('/files/6', $search->resolveTarget('files', 6)?->url);
    }
}
