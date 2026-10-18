<?php

namespace Leantime\Core\Events\Contracts;

/**
 * A class-based domain event that the browser should hear about too.
 *
 * Laravel pushes `ShouldBroadcast` events to the browser over a websocket; this does the same over
 * the `HX-Trigger` response header. When such an event is dispatched while handling an htmx request,
 * the dispatcher queues its client event name(s) on the response, so whatever listens with
 * `hx-trigger="lt:tickets:ticket.updated from:body"` refreshes without the controller having to
 * remember to emit anything. Requests that aren't htmx requests (JSON-RPC, MCP, full page loads,
 * queue jobs) are left alone.
 *
 * The implementing class declares its wire name as a constant so templates, components and
 * attributes can reference it statically:
 *
 *     final class TicketUpdated implements LeantimeEvent, EmitsClientEvent
 *     {
 *         use InteractsWithEvents, InteractsWithClientEvents;
 *
 *         public const CLIENT_EVENT = 'lt:tickets:ticket.updated';
 *
 *         public function clientScope(): int|string|null { return $this->ticketId; }
 *     }
 *
 * The name follows lt:{domain}:{entity}.{verb} (enforced by EventVocabularyTest). Listen with
 * `TicketUpdated::class` in `HxComponent::listensTo()` / `<x-global::hx :listen>`, or with
 * `{{ TicketUpdated::CLIENT_EVENT }}` in a hand-written `hx-trigger`.
 *
 * UI commands (toast, close modal) are not domain facts and stay on
 * {@see \Leantime\Core\Events\Htmx\HtmxUiEvents}.
 */
interface EmitsClientEvent
{
    /**
     * The client event names this dispatch puts on the `HX-Trigger` header: the broad name, plus
     * the entity-scoped name ("lt:tickets:ticket.updated#42") when the event knows its entity.
     *
     * @return array<int, string>
     */
    public function clientEvents(): array;
}
