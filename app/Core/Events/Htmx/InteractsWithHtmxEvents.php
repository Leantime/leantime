<?php

namespace Leantime\Core\Events\Htmx;

/**
 * Shared behavior for string-backed client (HTMX) event enums.
 *
 * The backing value of each case is the wire name. PHP enums cannot define __toString, so use
 * {@see event()} to obtain the wire name in PHP code; Blade's `{{ }}` renders the value directly
 * because Laravel's e() helper unwraps backed enums.
 */
trait InteractsWithHtmxEvents
{
    /**
     * The wire name (the enum's backing value).
     */
    public function event(): string
    {
        return $this->value;
    }

    /**
     * Wire name scoped to a single entity id, e.g. "lt:reactions:sentiment.updated#42".
     */
    public function scoped(int|string $id): string
    {
        return $this->value.'#'.$id;
    }

    /**
     * Format for an `hx-trigger` attribute, e.g. "lt:tickets:ticket.updated from:body".
     */
    public function trigger(string $from = 'body'): string
    {
        return $this->value.($from !== '' ? ' from:'.$from : '');
    }
}
