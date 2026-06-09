<?php

namespace Unit\app\Core\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeEvent;
use Leantime\Core\Events\Contracts\LeantimeFilter;
use Leantime\Core\Events\EventDispatcher;
use Unit\TestCase;

/**
 * Fixture event mirroring a migrated domain event: typed payload plus the
 * `legacyHook: __FUNCTION__` discriminator pattern — each dispatch rebuilds the single
 * historical name of its emit site (never a static list of all sites).
 */
class FixtureThingUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    public function __construct(
        public readonly int $thingId,
        private readonly ?string $legacyHook = null,
    ) {}

    public function legacyHooks(): array
    {
        if ($this->legacyHook === null) {
            return [];
        }

        return ['leantime.domain.things.services.things.'.$this->legacyHook.'.thing_updated'];
    }
}

/**
 * Fixture event without legacy hooks (an event introduced after the class-based system).
 */
class FixtureThingCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    public function __construct(public readonly int $thingId) {}
}

/**
 * Fixture class-based listener (resolved through the container, handle() receives the
 * typed event object).
 */
class FixtureThingListener
{
    public static array $received = [];

    public function handle(FixtureThingUpdated $event): void
    {
        self::$received[] = $event;
    }
}

/**
 * Fixture filter mirroring a migrated domain filter: payload plus typed context.
 */
class FixtureThingsFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    public function __construct(public array $things, public readonly int $userId) {}

    public function payload(): mixed
    {
        return $this->things;
    }

    public function legacyHooks(): array
    {
        return [
            'leantime.domain.things.services.things.getThings.filterThings',
        ];
    }
}

class ClassEventDispatchTest extends TestCase
{
    private array $staticSnapshot = [];

    private const STATIC_PROPS = [
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
        foreach (self::STATIC_PROPS as $prop) {
            $property = $reflection->getProperty($prop);
            $this->staticSnapshot[$prop] = $property->getValue();
        }

        FixtureThingListener::$received = [];
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->staticSnapshot as $prop => $value) {
            $property = $reflection->getProperty($prop);
            $property->setValue(null, $value);
        }

        parent::tearDown();
    }

    /**
     * A closure listener registered on the FQCN receives the bare typed event object.
     */
    public function test_fqcn_closure_listener_receives_typed_event_object(): void
    {
        $received = null;
        EventDispatcher::add_event_listener(FixtureThingUpdated::class, function ($event) use (&$received) {
            $received = $event;
        });

        FixtureThingUpdated::dispatch(thingId: 42);

        $this->assertInstanceOf(FixtureThingUpdated::class, $received);
        $this->assertSame(42, $received->thingId);
    }

    /**
     * A class-string listener registered on the FQCN is container-resolved and its
     * handle() method receives the typed event object. This is the cacheable
     * registration style new code should use (no closures).
     */
    public function test_fqcn_class_listener_handle_receives_typed_event_object(): void
    {
        EventDispatcher::add_event_listener(FixtureThingUpdated::class, FixtureThingListener::class);

        FixtureThingUpdated::dispatch(thingId: 7);

        $this->assertCount(1, FixtureThingListener::$received);
        $this->assertSame(7, FixtureThingListener::$received[0]->thingId);
    }

    /**
     * BACKWARDS COMPATIBILITY: a listener registered on the exact historical string name
     * fires and receives today's array payload (event properties + current_route +
     * currentEvent) — NOT the event object. Existing plugins keep working unchanged.
     */
    public function test_legacy_string_listener_receives_legacy_array_payload(): void
    {
        $received = null;
        EventDispatcher::add_event_listener(
            'leantime.domain.things.services.things.updateThing.thing_updated',
            function ($params) use (&$received) {
                $received = $params;
            }
        );

        FixtureThingUpdated::dispatch(thingId: 42, legacyHook: 'updateThing');

        $this->assertIsArray($received);
        $this->assertSame(42, $received['thingId']);
        $this->assertSame(
            'leantime.domain.things.services.things.updateThing.thing_updated',
            $received['currentEvent']
        );
        $this->assertArrayHasKey('current_route', $received);
    }

    /**
     * BACKWARDS COMPATIBILITY: plugin wildcard subscriptions (leantime.domain.*.services.*)
     * match the legacy name of a class-based event — exactly ONCE per dispatch, because
     * each emit site contributes only its own historical name via the legacyHook
     * discriminator. Both historical names stay reachable from their respective sites.
     */
    public function test_wildcard_listener_fires_once_per_dispatch_for_legacy_hook(): void
    {
        $called = 0;
        EventDispatcher::add_event_listener('leantime.domain.*.services.*', function () use (&$called) {
            $called++;
        });

        FixtureThingUpdated::dispatch(thingId: 1, legacyHook: 'updateThing');
        $this->assertSame(1, $called);

        FixtureThingUpdated::dispatch(thingId: 1, legacyHook: 'patchThing');
        $this->assertSame(2, $called);
    }

    /**
     * BACKWARDS COMPATIBILITY: an exact subscriber to one historical site's name does
     * NOT fire when a different site emits the same logical event — per-site semantics
     * are preserved through the migration window.
     */
    public function test_exact_legacy_listener_keeps_per_site_semantics(): void
    {
        $called = 0;
        EventDispatcher::add_event_listener(
            'leantime.domain.things.services.things.patchThing.thing_updated',
            function () use (&$called) {
                $called++;
            }
        );

        FixtureThingUpdated::dispatch(thingId: 1, legacyHook: 'updateThing');
        $this->assertSame(0, $called);

        FixtureThingUpdated::dispatch(thingId: 1, legacyHook: 'patchThing');
        $this->assertSame(1, $called);
    }

    /**
     * Wildcard string listeners do NOT accidentally match the FQCN (backslashes and
     * case don't fit the dotted lowercase patterns).
     */
    public function test_wildcard_listener_does_not_match_fqcn(): void
    {
        $called = 0;
        EventDispatcher::add_event_listener('leantime.*', function () use (&$called) {
            $called++;
        });

        FixtureThingCreated::dispatch(thingId: 1);

        $this->assertSame(0, $called);
    }

    /**
     * An event with no legacy hooks only reaches FQCN listeners.
     */
    public function test_event_without_legacy_hooks_fires_fqcn_listener_only(): void
    {
        $received = null;
        EventDispatcher::add_event_listener(FixtureThingCreated::class, function ($event) use (&$received) {
            $received = $event;
        });

        FixtureThingCreated::dispatch(thingId: 9);

        $this->assertInstanceOf(FixtureThingCreated::class, $received);
        $this->assertContains(FixtureThingCreated::class, EventDispatcher::get_available_hooks()['events']);
    }

    /**
     * FQCN listeners run in priority order, lower number first.
     */
    public function test_fqcn_listeners_run_in_priority_order(): void
    {
        $order = [];
        EventDispatcher::add_event_listener(FixtureThingCreated::class, function () use (&$order) {
            $order[] = 30;
        }, 30);
        EventDispatcher::add_event_listener(FixtureThingCreated::class, function () use (&$order) {
            $order[] = 10;
        }, 10);

        FixtureThingCreated::dispatch(thingId: 1);

        $this->assertSame([10, 30], $order);
    }

    /**
     * Class filter: FQCN listeners thread the payload and receive the filter object as
     * typed context; the final payload is returned.
     */
    public function test_class_filter_threads_payload_through_fqcn_listeners(): void
    {
        $receivedFilter = null;
        EventDispatcher::add_filter_listener(FixtureThingsFilter::class, function ($things, $filter) use (&$receivedFilter) {
            $receivedFilter = $filter;
            $things[] = 'added-by-listener';

            return $things;
        });

        $result = FixtureThingsFilter::dispatch(things: ['original'], userId: 5);

        $this->assertSame(['original', 'added-by-listener'], $result);
        $this->assertInstanceOf(FixtureThingsFilter::class, $receivedFilter);
        $this->assertSame(5, $receivedFilter->userId);
    }

    /**
     * BACKWARDS COMPATIBILITY: a filter listener on the historical string name receives
     * today's ($payload, $availableParams) signature — params include the filter's
     * public properties plus current_route/currentEvent — and its return value threads
     * into the final result, after FQCN listeners.
     */
    public function test_class_filter_threads_payload_through_legacy_listeners(): void
    {
        $receivedParams = null;

        EventDispatcher::add_filter_listener(FixtureThingsFilter::class, function ($things, $filter) {
            $things[] = 'fqcn';

            return $things;
        });

        EventDispatcher::add_filter_listener(
            'leantime.domain.things.services.things.getThings.filterThings',
            function ($things, $params) use (&$receivedParams) {
                $receivedParams = $params;
                $things[] = 'legacy';

                return $things;
            }
        );

        $result = FixtureThingsFilter::dispatch(things: ['original'], userId: 5);

        // FQCN group runs first, then the legacy group threads its output.
        $this->assertSame(['original', 'fqcn', 'legacy'], $result);
        $this->assertSame(5, $receivedParams['userId']);
        $this->assertArrayHasKey('current_route', $receivedParams);
    }

    /**
     * A filter with no listeners at all returns the payload unchanged.
     */
    public function test_class_filter_without_listeners_returns_payload_unchanged(): void
    {
        $result = FixtureThingsFilter::dispatch(things: ['untouched'], userId: 1);

        $this->assertSame(['untouched'], $result);
    }

    /**
     * The instance apply() ergonomic returns the filtered payload too.
     */
    public function test_class_filter_apply_instance_method(): void
    {
        EventDispatcher::add_filter_listener(FixtureThingsFilter::class, function ($things) {
            $things[] = 'applied';

            return $things;
        });

        $filter = new FixtureThingsFilter(things: ['a'], userId: 2);

        $this->assertSame(['a', 'applied'], $filter->apply());
    }

    /**
     * Class events route correctly through Laravel's event() helper / the instance
     * dispatch() of the Dispatcher interface as well.
     */
    public function test_class_event_routes_through_laravel_event_helper(): void
    {
        $received = null;
        EventDispatcher::add_event_listener(FixtureThingCreated::class, function ($event) use (&$received) {
            $received = $event;
        });

        event(new FixtureThingCreated(thingId: 3));

        $this->assertInstanceOf(FixtureThingCreated::class, $received);
        $this->assertSame(3, $received->thingId);
    }
}
