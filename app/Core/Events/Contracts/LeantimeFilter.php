<?php

namespace Leantime\Core\Events\Contracts;

/**
 * Contract for class-based filters (the return-value pipeline counterpart of events).
 *
 * Filter classes live next to events in Events/, are named {Thing}Filter
 * (TodoWidgetTasksFilter), hold the initial payload plus typed context as public
 * (constructor-promoted) properties, and return the filtered payload from apply().
 *
 * Listeners subscribe to the class itself and keep the familiar filter signature —
 * they receive the current payload and the filter object as context, and must return
 * the (possibly modified) payload:
 *
 *     EventDispatcher::add_filter_listener(TodoWidgetTasksFilter::class,
 *         fn ($tickets, TodoWidgetTasksFilter $filter) => $tickets);
 *
 * MIGRATION WINDOW: legacyHooks() returns the exact historical string name(s) of the
 * CURRENT emit site; the dispatcher threads the payload through listeners on the FQCN
 * first, then through each legacy name where listeners receive today's
 * ($payload, $availableParams) array signature unchanged. When several methods
 * historically ran the same raw hook, rebuild the right name from a
 * `legacyHook: __FUNCTION__` constructor discriminator — never statically list all
 * sites. See {@see LeantimeEvent} for the full rationale.
 *
 * Use the {@see \Leantime\Core\Events\Concerns\InteractsWithFilters} trait for the
 * payload()/apply() plumbing and the default empty legacyHooks().
 */
interface LeantimeFilter
{
    /**
     * The initial payload to thread through the filter pipeline.
     */
    public function payload(): mixed;

    /**
     * The exact historical dotted string name(s) the CURRENT emit site ran under.
     * Use a `legacyHook: __FUNCTION__` constructor discriminator when several methods
     * historically ran the same raw hook (see class docblock).
     *
     * @return array<int, string>
     */
    public function legacyHooks(): array;
}
