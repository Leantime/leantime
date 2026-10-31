<?php

namespace Unit\app\Domain\Reactions\Services;

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Projects\Events\ProjectFavoriteAdded;
use Leantime\Domain\Projects\Events\ProjectFavoriteRemoved;
use Leantime\Domain\Reactions\Repositories\Reactions as ReactionsRepository;
use Leantime\Domain\Reactions\Services\Reactions as ReactionsService;
use Unit\TestCase;

/**
 * Every reaction write goes through addReaction()/removeReaction(), so a project favorite is
 * reported there (ProjectFavoriteAdded / ProjectFavoriteRemoved → lt:projects:favorite.added /
 * .removed), whether the htmx project card or a JSON-RPC client changed it.
 */
class ProjectFavoriteEventsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, object> The favorite events dispatched. */
    private array $events = [];

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

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_PROPS as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->events = [];
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

        parent::tearDown();
    }

    public function test_adding_a_project_favorite_reports_it(): void
    {
        $this->assertTrue($this->service(writeSucceeds: true)->addReaction(3, 'project', 12, 'favorite'));

        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(ProjectFavoriteAdded::class, $this->events[0]);
        $this->assertSame(12, $this->events[0]->projectId);
        $this->assertSame(3, $this->events[0]->userId);
        $this->assertSame(['lt:projects:favorite.added', 'lt:projects:favorite.added#12'], $this->events[0]->clientEvents());
    }

    public function test_removing_a_project_favorite_reports_it(): void
    {
        $this->assertTrue($this->service(writeSucceeds: true)->removeReaction(3, 'project', 12, 'favorite'));

        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(ProjectFavoriteRemoved::class, $this->events[0]);
        $this->assertSame(12, $this->events[0]->projectId);
    }

    public function test_a_failed_write_reports_nothing(): void
    {
        $service = $this->service(writeSucceeds: false);

        $service->addReaction(3, 'project', 12, 'favorite');
        $service->removeReaction(3, 'project', 12, 'favorite');

        $this->assertSame([], $this->events);
    }

    public function test_other_reactions_are_not_project_favorites(): void
    {
        $service = $this->service(writeSucceeds: true);

        $service->addReaction(3, 'ticket', 12, 'favorite');
        $service->addReaction(3, 'project', 12, 'like');
        $service->removeReaction(3, 'comment', 12, 'favorite');

        $this->assertSame([], $this->events);
    }

    private function service(bool $writeSucceeds): ReactionsService
    {
        $repository = $this->make(ReactionsRepository::class, [
            'getUserReactions' => fn () => [],
            'addReaction' => fn () => $writeSucceeds,
            'removeUserReaction' => fn () => $writeSucceeds,
        ]);

        return new ReactionsService($repository, $this->make(CommentRepository::class));
    }
}
