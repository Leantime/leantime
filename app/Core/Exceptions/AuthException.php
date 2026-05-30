<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * @deprecated Use {@see AuthorizationException} instead.
 *
 * Thin, deprecated alias of {@see AuthorizationException} (HTTP 403 / JSON-RPC -32001), kept
 * only because the AdvancedAuth plugin — and potentially external installs — still throw this
 * class name (e.g. AdvancedAuth\Listeners\CheckDomain). It carries no behaviour of its own
 * beyond preserving the legacy ($message, $code) constructor signature, so it is NOT a second
 * authorization exception — it IS an AuthorizationException.
 */
class AuthException extends AuthorizationException
{
    /**
     * @param  string  $message  The exception message.
     * @param  int  $code  HTTP status, also exposed via getStatusCode() and getCode() (default 403).
     * @param  Throwable|null  $previous  Previous throwable for chaining.
     */
    public function __construct(string $message = '', int $code = 403, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
        $this->statusCode = $code;
        $this->code = $code;
    }
}
