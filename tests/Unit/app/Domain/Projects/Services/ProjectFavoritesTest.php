<?php

namespace Unit\app\Domain\Projects\Services;

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Projects\Events\ProjectFavoriteAdded;
use Leantime\Domain\Projects\Events\ProjectFavoriteRemoved;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reactions\Services\Reactions as ReactionsService;
use Unit\TestCase;

/**
 * Favoriting a project stores a "favorite" reaction and reports it as ProjectFavoriteAdded /
 * ProjectFavoriteRemoved (lt:projects:favorite.added / .removed), so project lists re-sort.
 */
class ProjectFavoritesTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, object> The favorite events dispatched. */
    private array $events = [];

    /** @var array<int, array<int, mixed>> The reaction calls the stubbed Reactions service received. */
    private array $reactionCalls = [];

    private array $dispatcherSnapshot = [];

    private const DISPATCHER_PROPS = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 3, 'role' => 'editor', 'name' => 'Caller']]);

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_PROPS as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->events = [];
        $this->reactionCalls = [];
        EventDispatcher::add_event_listener(ProjectFavoriteAdded::class, function (ProjectFavoriteAdded $event) {
            $this->events[] = $event;
        });
        EventDispatcher::add_event_listener(ProjectFavoriteRemoved::class, function (ProjectFavoriteRemoved $event) {
            $this->events[] = $event;
        });
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }
        app()->forgetInstance(ReactionsService::class);

        parent::tearDown();
    }

    public function test_favorite_stores_the_reaction_and_reports_it(): void
    {
        $this->useReactions(succeeds: true);

        $this->assertTrue($this->service()->favorite(12));

        $this->assertSame([['react', 'project', 12, 'favorite']], $this->reactionCalls);
        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(ProjectFavoriteAdded::class, $this->events[0]);
        $this->assertSame(12, $this->events[0]->projectId);
        $this->assertSame(3, $this->events[0]->userId);
        $this->assertSame(['lt:projects:favorite.added', 'lt:projects:favorite.added#12'], $this->events[0]->clientEvents());
    }

    public function test_unfavorite_removes_the_reaction_and_reports_it(): void
    {
        $this->useReactions(succeeds: true);

        $this->assertTrue($this->service()->unfavorite(12));

        $this->assertSame([['unreact', 'project', 12, 'favorite']], $this->reactionCalls);
        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(ProjectFavoriteRemoved::class, $this->events[0]);
    }

    public function test_a_denied_or_failed_favorite_reports_nothing(): void
    {
        $this->useReactions(succeeds: false);

        $this->assertFalse($this->service()->favorite(12));
        $this->assertFalse($this->service()->unfavorite(12));

        $this->assertSame([], $this->events);
    }

    private function useReactions(bool $succeeds): void
    {
        app()->instance(ReactionsService::class, $this->make(ReactionsService::class, [
            'react' => function (string $module, int $moduleId, string $reaction) use ($succeeds) {
                $this->reactionCalls[] = ['react', $module, $moduleId, $reaction];

                return $succeeds;
            },
            'unreact' => function (string $module, int $moduleId, string $reaction) use ($succeeds) {
                $this->reactionCalls[] = ['unreact', $module, $moduleId, $reaction];

                return $succeeds;
            },
        ]));
    }

    private function service(): ProjectService
    {
        return $this->make(ProjectService::class);
    }
}
