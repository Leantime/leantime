<?php

namespace Leantime\Core\Events\Htmx;

/**
 * Contract for client (HTMX) event enums.
 *
 * Client events travel from the server to the browser on the `HX-Trigger` response header and are
 * consumed either declaratively (`hx-trigger="<event> from:body"`) for data events, or by a JS
 * listener for UI command events. Implementations are string-backed enums whose backing value IS
 * the wire name, following the convention:
 *
 *   - data events:    lt:{domain}:{entity}.{verb}   e.g. lt:tickets:ticket.updated
 *   - UI commands:    lt:ui:{command}               e.g. lt:ui:modal.close
 *
 * Use the {@see InteractsWithHtmxEvents} trait to satisfy this interface. Note: PHP enums cannot
 * implement Stringable / __toString, so use {@see event()} (or `->value`) to get the wire name in
 * PHP. In Blade, `{{ MyEvents::Case }}` renders the value (Laravel's e() unwraps backed enums).
 */
interface HtmxEvent
{
    /**
     * The wire name (the enum's backing value), e.g. "lt:tickets:ticket.updated".
     */
    public function event(): string;

    /**
     * Wire name scoped to a single entity, e.g. "lt:reactions:sentiment.updated#42".
     * Lets a specific component listen for changes to one entity while broad listeners use the
     * unscoped name.
     */
    public function scoped(int|string $id): string;

    /**
     * The value formatted for an `hx-trigger` attribute, e.g. "lt:tickets:ticket.updated from:body".
     */
    public function trigger(string $from = 'body'): string;
}
