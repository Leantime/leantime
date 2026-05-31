<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * An entity being created already exists — a conflict (HTTP 409 / JSON-RPC -32005).
 *
 * A {@see LeantimeException} so the 409 status is honored across all surfaces.
 */
class EntityExistsException extends LeantimeException
{
    protected int $rpcCode = -32005;

    /**
     * @param  string  $message  The exception message.
     * @param  int  $code  HTTP status, also exposed via getStatusCode() and getCode().
     * @param  Throwable|null  $previous  Previous throwable for chaining.
     */
    public function __construct(string $message = '', int $code = 409, ?Throwable $previous = null)
    {
        $this->statusCode = $code;
        parent::__construct($message, $code, $previous);
    }
}
