<?php

namespace Leantime\Core\Events\Htmx;

/**
 * Helpers for building the `HX-Trigger` response header from client event names.
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
            $name = $name instanceof HtmxEvent ? $name->event() : (string) $name;
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
