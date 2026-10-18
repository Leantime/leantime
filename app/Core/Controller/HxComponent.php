<?php

namespace Leantime\Core\Controller;

use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Htmx\HtmxEvent;

/**
 * Base class for HTMX-backed components.
 *
 * An HxComponent is an {@see HtmxController} that also declares its event contract — the route it
 * is fetched from, the events that should make it re-fetch ({@see listensTo}), and the events it
 * emits when its actions mutate data ({@see emits}). The `<x-global::hx :for="...::class">` mount
 * component reads this contract to auto-wire `hx-get`/`hx-trigger`, so the emit side and the listen
 * side reference the SAME event and can never drift apart (the class of bug where a template
 * listens for `subtasksUpdated` while the controller emits `subtasks_update`).
 *
 * Events are named by their domain event class (SubtasksUpdated::class), which implements
 * {@see \Leantime\Core\Events\Contracts\EmitsClientEvent}: the service that dispatches it puts
 * it on the response, so the component's actions don't emit it by hand.
 *
 * Plain {@see HtmxController}s remain valid; declaring the contract is opt-in. Components that don't
 * extend this can still be mounted with explicit `endpoint`/`listen` attributes on `<x-global::hx>`.
 *
 * @method string|null run() Inherited fallback action.
 */
abstract class HxComponent extends HtmxController
{
    /** The action invoked when the component is first mounted (its "render me" endpoint). */
    public static string $mountAction = 'get';

    /** Default `hx-swap` strategy for the mount wrapper. */
    public static string $swap = 'outerHTML';

    /**
     * The hx route segment, e.g. "tickets/timerButton" → /hx/tickets/timerButton/{action}.
     */
    abstract public static function route(): string;

    /**
     * Events that should cause this component to re-fetch itself.
     *
     * @return array<int, class-string<EmitsClientEvent>|HtmxEvent>
     */
    public static function listensTo(): array
    {
        return [];
    }

    /**
     * Events this component's actions cause (contract/documentation; the actual emission happens
     * when the service dispatches the domain event, or via {@see \Leantime\Core\UI\Template::emit()}).
     *
     * @return array<int, class-string<EmitsClientEvent>|HtmxEvent>
     */
    public static function emits(): array
    {
        return [];
    }
}
