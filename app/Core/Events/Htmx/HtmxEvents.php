<?php

namespace Leantime\Core\Events\Htmx;

use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;

/**
 * Helpers for building the `HX-Trigger` response header from client event names.
 *
 * A client event can be named three ways, all resolved by {@see name()}: a domain event class that
 * implements {@see EmitsClientEvent} (TicketUpdated::class — preferred for data events), an
 * {@see HtmxEvent} enum case (HtmxUiEvents::Notify — UI commands), or a raw string.
 */
final class HtmxEvents
{
    /**
     * The wire name of a client event given as an enum case, an EmitsClientEvent class name or a
     * raw string, e.g. TicketUpdated::class → "lt:tickets:ticket.updated".
     */
    public static function name(HtmxEvent|string $event): string
    {
        if ($event instanceof HtmxEvent) {
            return $event->event();
        }

        if (str_contains($event, '\\') && is_subclass_of($event, EmitsClientEvent::class)) {
            return $event::CLIENT_EVENT;
        }

        return $event;
    }

    /**
     * The wire name scoped to one entity, e.g. "lt:tickets:subtask.updated#42".
     */
    public static function scoped(HtmxEvent|string $event, int|string $id): string
    {
        return self::name($event).'#'.$id;
    }

    /**
     * Queue a dispatched domain event's client names on the current htmx response. Called by the
     * EventDispatcher for every {@see EmitsClientEvent}; a no-op outside htmx requests so JSON-RPC,
     * MCP, full page loads and queue jobs never carry an HX-Trigger header.
     */
    public static function queueFor(EmitsClientEvent $event): void
    {
        $request = app('request');
        if (! $request instanceof IncomingRequest || ! $request->isHtmxRequest()) {
            return;
        }

        app(Template::class)->emit(...$event->clientEvents());
    }

    /**
     * Build the comma-separated `HX-Trigger` header value from queued event names, de-duplicated
     * and order-stable. Scoped names (e.g. "lt:tickets:ticket.updated#42") pass through unchanged.
     *
     * @param  array<int, string|HtmxEvent>  $names
     */
    public static function triggerHeader(array $names): string
    {
        $wireNames = array_map(self::name(...), $names);

        return implode(',', array_values(array_unique($wireNames)));
    }
}
