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
 *
 * MIGRATION WINDOW: while emitters and listeners move to the lt:{domain}:{entity}.{verb} convention
 * we dual-emit legacy names alongside their canonical replacement so existing declarative listeners
 * (hx-trigger="<name> from:body") and (phar) plugins keep working without coordinated releases. Each
 * group is bidirectional — emitting ANY member puts the whole group on the wire, so old and new
 * listeners both fire regardless of which name the emitter used. Delete LEGACY_ALIASES once every
 * emitter and listener has been migrated.
 *
 * Only DOMAIN data events are aliased here. UI command events (lt:ui:*) are intentionally NOT
 * aliased: they're consumed by JS addEventListener handlers that listen for each name directly, so
 * dual-emitting them would fire the same handler once per alias (double growl, multiple modal-close
 * callbacks) for a single response.
 */
final class HtmxEvents
{
    /**
     * Bidirectional alias groups. The first entry of each group is the canonical lt:* name.
     *
     * @var array<int, array<int, string>>
     */
    private const LEGACY_ALIASES = [
        ['lt:tickets:ticket.updated', 'ticket_update'],
        ['lt:tickets:subtask.updated', 'subtasks_update', 'subtasksUpdated'],
        ['lt:projects:project.updated', 'HTMX.updateProjectList'],
        ['lt:timesheets:timer.updated', 'timerUpdate'],
    ];

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
     * Expand event names to include their legacy/canonical aliases, de-duplicated and order-stable.
     * Scoped names (e.g. "lt:reactions:sentiment.updated#42") pass through unchanged.
     *
     * @param  array<int, string|HtmxEvent>  $names
     * @return array<int, string>
     */
    public static function expand(array $names): array
    {
        $expanded = [];

        foreach ($names as $name) {
            $name = self::name($name);
            $expanded[] = $name;

            foreach (self::LEGACY_ALIASES as $group) {
                if (in_array($name, $group, true)) {
                    array_push($expanded, ...$group);
                }
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * Build the comma-separated `HX-Trigger` header value from queued event names.
     *
     * @param  array<int, string|HtmxEvent>  $names
     */
    public static function triggerHeader(array $names): string
    {
        return implode(',', self::expand($names));
    }
}
