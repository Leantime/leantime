<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * Invalid argument supplied to an operation (HTTP 422 / JSON-RPC -32602 invalid params).
 *
 * Now a {@see LeantimeException}; for user-facing input validation prefer
 * {@see ValidationException}, which additionally carries a per-field error map.
 */
class InvalidArgumentException extends LeantimeException
{
    protected int $rpcCode = -32602;

    /**
     * @param  string  $message  The exception message.
     * @param  int  $code  HTTP status, also exposed via getStatusCode() and getCode().
     * @param  Throwable|null  $previous  Previous throwable for chaining.
     */
    public function __construct(string $message = '', int $code = 422, ?Throwable $previous = null)
    {
        $this->statusCode = $code;
        parent::__construct($message, $code, $previous);
    }
}
