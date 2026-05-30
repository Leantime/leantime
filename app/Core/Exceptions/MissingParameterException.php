<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * A required parameter was missing (HTTP 422 / JSON-RPC -32602 invalid params).
 *
 * A degenerate validation failure. Now a {@see LeantimeException}, so a service throwing
 * this during a JSON-RPC call surfaces as a proper -32602 "Invalid params" instead of a
 * generic server error.
 */
class MissingParameterException extends LeantimeException
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
