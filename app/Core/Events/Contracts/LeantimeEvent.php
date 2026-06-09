<?php

namespace Leantime\Core\Events\Contracts;

/**
 * Contract for class-based domain events.
 *
 * Event classes live in app/Domain/{Domain}/Events/ (or app/Core/{Module}/Events/ for
 * core modules), are named {Entity}{Verb} with the verb taken from the central
 * {@see \Leantime\Core\Events\EventVerb} vocabulary, and carry their typed payload as
 * public (constructor-promoted) properties.
 *
 * Listeners subscribe to the class itself:
 *
 *     EventDispatcher::add_event_listener(TicketUpdated::class, MyListener::class);
 *
 * and receive the bare event object — `MyListener::handle(TicketUpdated $event)`.
 *
 * MIGRATION WINDOW: legacyHooks() returns the exact historical string names that the
 * same logical event used to fire under (the auto-generated
 * leantime.domain.{...}.{method}.{rawHook} strings). The dispatcher dual-emits to those
 * names so existing string/wildcard listeners — plugins in particular — keep firing
 * with today's array payload, without coordinated releases. Remove the entries (and
 * eventually the mechanism) once all consumers have migrated to the FQCN. Mirrors the
 * client-side {@see \Leantime\Core\Events\Htmx\HtmxEvents} LEGACY_ALIASES window.
 *
 * Use the {@see \Leantime\Core\Events\Concerns\InteractsWithEvents} trait for the
 * static dispatch() ergonomic and the default empty legacyHooks().
 */
interface LeantimeEvent
{
    /**
     * The exact historical dotted string names this event used to fire under.
     * One entry per emitting method when the same raw hook fired from several methods.
     * Empty for events introduced after the class-based system.
     *
     * @return array<int, string>
     */
    public function legacyHooks(): array;
}
