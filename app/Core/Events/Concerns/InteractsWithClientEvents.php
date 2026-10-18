<?php

namespace Leantime\Core\Events\Concerns;

/**
 * Default {@see \Leantime\Core\Events\Contracts\EmitsClientEvent} behavior for events that declare
 * a `CLIENT_EVENT` constant.
 *
 * Override {@see clientScope()} to also emit the entity-scoped name, which is what a component
 * mounted for one entity (`<x-global::hx :for="..." :id="$ticketId">`) listens for.
 */
trait InteractsWithClientEvents
{
    /**
     * The entity id that scopes this event on the client, or null when it has none.
     */
    public function clientScope(): int|string|null
    {
        return null;
    }

    /**
     * The broad client event name plus, when the event has a scope, the scoped name.
     *
     * @return array<int, string>
     */
    public function clientEvents(): array
    {
        $scope = $this->clientScope();

        if ($scope === null || $scope === '') {
            return [static::CLIENT_EVENT];
        }

        return [static::CLIENT_EVENT, static::CLIENT_EVENT.'#'.$scope];
    }
}
