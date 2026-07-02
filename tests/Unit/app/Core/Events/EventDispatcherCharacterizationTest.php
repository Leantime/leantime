<?php

namespace Unit\app\Core\Events;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\WorkStructure\Events\StructureRegistered;
use Unit\TestCase;

/**
 * Fixture emitter that dispatches through the DispatchesEvents trait exactly like a
 * domain service does, so the auto-generated event names (lowercased FQCN + method +
 * raw hook) match the real runtime format.
 */
class CharacterizationEmitter
{
    use DispatchesEvents;

    public function updateThing(): void
    {
        self::dispatchEvent('thing_updated', ['thingId' => 7]);
    }

    public function filterThing(int $payload): mixed
    {
        return self::dispatchFilter('thing_filter', $payload, ['mode' => 'strict']);
    }
}

/**
 * Characterization tests locking the CURRENT EventDispatcher behavior before the
 * class-based event bridge is added. These tests document the string-event contract
 * that existing plugins rely on; they must keep passing unchanged.
 */
class EventDispatcherCharacterizationTest extends TestCase
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
     * The DispatchesEvents trait builds the full event name as
     * strtolower(FQCN with \ -> .) + '.' + emitting method + '.' + raw hook.
     * Plugins subscribe to exactly these strings — the format must not drift.
     */
    public function test_trait_builds_full_event_name_from_class_and_method(): void
    {
        (new CharacterizationEmitter)->updateThing();

        $this->assertContains(
            'unit.app.core.events.characterizationemitter.updateThing.thing_updated',
            EventDispatcher::get_available_hooks()['events']
        );
    }

    /**
     * A listener registered on the full string name receives a SINGLE array argument:
     * the dispatched payload merged with current_route and currentEvent.
     */
    public function test_string_event_listener_receives_define_params_array(): void
    {
        $received = null;
        EventDispatcher::add_event_listener(
            'unit.app.core.events.characterizationemitter.updateThing.thing_updated',
            function ($params) use (&$received) {
                $received = $params;
            }
        );

        (new CharacterizationEmitter)->updateThing();

        $this->assertIsArray($received);
        $this->assertSame(7, $received['thingId']);
        $this->assertSame(
            'unit.app.core.events.characterizationemitter.updateThing.thing_updated',
            $received['currentEvent']
        );
        $this->assertArrayHasKey('current_route', $received);
    }

    /**
     * Filter listeners receive ($payload, $availableParams) where availableParams is the
     * emitter-provided context merged with current_route/currentEvent, and the payload is
     * threaded through listeners in priority order (lower priority number runs first).
     */
    public function test_filter_threads_payload_in_priority_order_and_passes_params(): void
    {
        $fullName = 'unit.app.core.events.characterizationemitter.filterThing.thing_filter';
        $receivedParams = null;

        EventDispatcher::add_filter_listener($fullName, function ($payload, $params) use (&$receivedParams) {
            $receivedParams = $params;

            return $payload + 1;
        }, 20);

        EventDispatcher::add_filter_listener($fullName, function ($payload, $params) {
            return $payload * 2;
        }, 10);

        $result = (new CharacterizationEmitter)->filterThing(5);

        // priority 10 runs first: 5 * 2 = 10, then priority 20: 10 + 1 = 11
        $this->assertSame(11, $result);
        $this->assertSame('strict', $receivedParams['mode']);
        $this->assertSame($fullName, $receivedParams['currentEvent']);
        $this->assertArrayHasKey('current_route', $receivedParams);
    }

    /**
     * Plugins rely on wildcard subscriptions (e.g. leantime.domain.*.services.*) matching
     * the auto-generated full names. The * wildcard must keep matching.
     */
    public function test_wildcard_listener_matches_full_event_name(): void
    {
        $called = 0;
        EventDispatcher::add_event_listener('leantime.domain.*.services.*', function () use (&$called) {
            $called++;
        });

        EventDispatcher::dispatch_event('leantime.domain.faux.services.faux.doIt.did_it', ['x' => 1], '');

        $this->assertSame(1, $called);
    }

    /**
     * Event listeners for one hook run in priority order, lower number first.
     */
    public function test_event_listeners_run_in_priority_order(): void
    {
        $order = [];
        EventDispatcher::add_event_listener('char.priority.event', function () use (&$order) {
            $order[] = 30;
        }, 30);
        EventDispatcher::add_event_listener('char.priority.event', function () use (&$order) {
            $order[] = 10;
        }, 10);
        EventDispatcher::add_event_listener('char.priority.event', function () use (&$order) {
            $order[] = 20;
        }, 20);

        EventDispatcher::dispatch_event('char.priority.event', [], '');

        $this->assertSame([10, 20, 30], $order);
    }

    /**
     * Current behavior for plain object events (Laravel Dispatchable path, e.g. the
     * WorkStructure events): the object resolves to its FQCN as the listener name and a
     * 'leantime' source listener receives the defineParams array with the object at [0].
     */
    public function test_plain_object_event_fires_fqcn_string_listener(): void
    {
        $received = null;
        EventDispatcher::add_event_listener(StructureRegistered::class, function ($params) use (&$received) {
            $received = $params;
        });

        StructureRegistered::dispatch(1, 'My Structure', 'system');

        $this->assertIsArray($received);
        $this->assertInstanceOf(StructureRegistered::class, $received[0]);
        $this->assertSame(1, $received[0]->structureId);
    }

    /**
     * The pattern-match cache is invalidated when a listener is added (version counter),
     * so listeners registered after a first dispatch still fire on later dispatches.
     */
    public function test_pattern_cache_busts_when_listener_added_after_dispatch(): void
    {
        $first = 0;
        $second = 0;

        EventDispatcher::add_event_listener('char.cache.*', function () use (&$first) {
            $first++;
        });
        EventDispatcher::dispatch_event('char.cache.bust', [], '');

        EventDispatcher::add_event_listener('char.cache.*', function () use (&$second) {
            $second++;
        });
        EventDispatcher::dispatch_event('char.cache.bust', [], '');

        $this->assertSame(2, $first);
        $this->assertSame(1, $second);
    }
}
