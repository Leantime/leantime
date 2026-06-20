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
 * MIGRATION WINDOW: legacyHooks() returns the exact historical string name(s) the
 * CURRENT emit site fired under (the auto-generated
 * leantime.domain.{...}.{method}.{rawHook} strings). The dispatcher dual-emits to those
 * names so existing string/wildcard listeners — plugins in particular — keep firing
 * with today's array payload, without coordinated releases.
 *
 * IMPORTANT: never statically list ALL historical emit sites — that would fire every
 * site's name on every dispatch (exact subscribers fire under the wrong conditions,
 * wildcard subscribers fire once per name instead of once per event). When the same
 * raw hook historically fired from several methods, take a constructor discriminator
 * and have each call site pass its own method name — `legacyHook: __FUNCTION__` — so
 * each dispatch rebuilds the single name that site produced (see the Tickets pilot
 * events for the pattern).
 *
 * Remove the entries (and eventually the mechanism) once all consumers have migrated
 * to the FQCN. Mirrors the client-side
 * {@see \Leantime\Core\Events\Htmx\HtmxEvents} LEGACY_ALIASES window.
 *
 * Use the {@see \Leantime\Core\Events\Concerns\InteractsWithEvents} trait for the
 * static dispatch() ergonomic and the default empty legacyHooks().
 */
interface LeantimeEvent
{
    /**
     * The exact historical dotted string name(s) the CURRENT emit site fired under —
     * at most one name per dispatch in practice. When several methods historically
     * emitted the same raw hook, rebuild the right name from a
     * `legacyHook: __FUNCTION__` constructor discriminator; never statically list all
     * sites (see class docblock). Empty for events introduced after the class-based
     * system.
     *
     * @return array<int, string>
     */
    public function legacyHooks(): array;
}
