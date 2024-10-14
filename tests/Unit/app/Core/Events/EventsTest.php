<?php

namespace Unit\app\Core\Events;

use Codeception\Test\Unit;
use Leantime\Core\Events\EventDispatcher;

class EventsTest extends Unit
{
    /**
     * This test will check the dispatch_event method of the EventDispatcher class.
     * It will dispatch an event and assert if it is added to the available_hooks array.
     */
    public function testDispatchEvent()
    {
        $eventName = 'test.event.name';
        $payload = ['testKey' => 'testValue'];
        $context = 'testContext';

        // Dispatch event
        EventDispatcher::dispatch_event($eventName, $payload, $context);

        // Get all available hooks
        $available_hooks = EventDispatcher::get_available_hooks();

        // Test that the dispatched event has been registered in available_hooks
        $this->assertContains("$context.$eventName", $available_hooks["events"]);
    }


    /**
     * This test will check the findEventListeners method of the EventDispatcher class.
     */
    public function testFindEventListeners()
    {
        $eventName = 'test.event.name';
        $listenerName = 'test.listener';
        $payload = ['testKey' => 'testValue'];
        $context = 'testContext';
        $eventListeners = [$listenerName => [$payload]];

        EventDispatcher::add_event_listener($listenerName, function () {
        }, 10);
        // Test that the event listener has been found
        $this->assertEquals([$payload], EventDispatcher::findEventListeners($listenerName, $eventListeners));
    }

    /**
     * This test will check the get_registries method of the EventDispatcher class.
     * It will add new event listener and a new filter listener and check both listeners
     * are in the registry arrays.
     */
    public function testGetRegistries()
    {
        $eventName = 'event.test.name';
        $filterName = 'filter.test.name';

        // Add an event listener
        EventDispatcher::add_event_listener($eventName, function () {
        }, 10);

        // Add a filter listener
        EventDispatcher::add_filter_listener($filterName, function () {
        }, 10);

        // Get registries
        $registries = EventDispatcher::get_registries();

        // Check registries
        $this->assertContains($eventName, $registries['events']);
        $this->assertContains($filterName, $registries['filters']);
    }
}
