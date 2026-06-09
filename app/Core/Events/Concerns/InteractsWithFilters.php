<?php

namespace Leantime\Core\Events\Concerns;

use Leantime\Core\Events\EventDispatcher;

/**
 * Shared behavior for class-based filters.
 *
 * Provides the static dispatch() / instance apply() ergonomics and sensible defaults
 * for the LeantimeFilter contract:
 *
 *     $tickets = TodoWidgetTasksFilter::dispatch(tickets: $tickets, userId: $userId);
 *
 * The default payload() returns the $payload property; filter classes that name their
 * payload something more meaningful (e.g. public array $tickets) override payload().
 */
trait InteractsWithFilters
{
    /**
     * Default: no legacy string names. Override during the migration window with the
     * exact historical leantime.* name of the CURRENT emit site — rebuilt from a
     * `legacyHook: __FUNCTION__` constructor discriminator when several methods
     * historically ran the same raw hook (see the LeantimeFilter docblock).
     *
     * @return array<int, string>
     */
    public function legacyHooks(): array
    {
        return [];
    }

    /**
     * Default payload accessor. Override when the payload property has a domain name.
     */
    public function payload(): mixed
    {
        return $this->payload;
    }

    /**
     * Construct the filter and run the pipeline, returning the filtered payload.
     * Arguments (named arguments included) are forwarded to the constructor.
     */
    public static function dispatch(mixed ...$args): mixed
    {
        return EventDispatcher::dispatch_class_filter(new static(...$args));
    }

    /**
     * Run the pipeline for an already-constructed filter, returning the filtered payload.
     */
    public function apply(): mixed
    {
        return EventDispatcher::dispatch_class_filter($this);
    }
}
