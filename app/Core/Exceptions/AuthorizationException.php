<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * Thrown when an authenticated user is not allowed to perform an action or access a resource.
 *
 * Renders as HTTP 403 on the web/REST surfaces and JSON-RPC error -32001 (an
 * implementation-defined code in the reserved -32000..-32099 range) on /api/jsonrpc.
 *
 * Services should throw this instead of returning `false`/`[]` on an authorization failure,
 * so the denial is unambiguous (today `return []`/`return false` collide with "no results"
 * and "not found").
 */
class AuthorizationException extends LeantimeException
{
    protected int $statusCode = 403;

    protected int $rpcCode = -32001;

    public function __construct(string $message = 'You are not allowed to perform this action.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
