<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * Thrown when a specifically requested resource does not exist.
 *
 * Renders as HTTP 404 on the web/REST surfaces and JSON-RPC error -32002 on /api/jsonrpc.
 *
 * Use this for a missing *single* requested entity (e.g. getTicket(99) where 99 is gone) —
 * NOT for an empty list/query result, which should still return `[]`. Throwing here keeps
 * "not found" distinct from "no permission" (today both collapse to `false`).
 */
class NotFoundException extends LeantimeException
{
    protected int $statusCode = 404;

    protected int $rpcCode = -32002;

    public function __construct(string $message = 'The requested resource could not be found.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
