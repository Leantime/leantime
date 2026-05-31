<?php

namespace Leantime\Core\Events\Htmx;

/**
 * Helpers for building the `HX-Trigger` response header from client event names.
 *
 * MIGRATION WINDOW: while emitters and listeners move to the lt:{domain}:{entity}.{verb} /
 * lt:ui:{command} convention we dual-emit legacy names alongside their canonical replacement so
 * existing JS listeners and (phar) plugins keep working without coordinated releases. Each group is
 * bidirectional — emitting ANY member puts the whole group on the wire, so old and new listeners
 * both fire regardless of which name the emitter used. Delete LEGACY_ALIASES once every emitter and
 * listener has been migrated.
 */
final class HtmxEvents
{
    /**
     * Bidirectional alias groups. The first entry of each group is the canonical lt:* name.
     *
     * @var array<int, array<int, string>>
     */
    private const LEGACY_ALIASES = [
        ['lt:ui:notify', 'HTMX.ShowNotification'],
        ['lt:ui:modal.close', 'closeModal', 'HTMX.closemodal', 'Htmx.CloseModal'],
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
