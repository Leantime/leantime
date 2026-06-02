<?php

namespace Leantime\Core\Exceptions;

use Throwable;

/**
 * Thrown when a JSON-RPC call targets a method gated by a plugin that is not enabled.
 *
 * Renders as HTTP 403 on the web/REST surfaces and JSON-RPC error -32004 (an
 * implementation-defined code in the reserved -32000..-32099 range) on /api/jsonrpc.
 *
 * Paired with the RequiresPlugin attribute — the Jsonrpc dispatcher throws this when
 * the named plugin is missing or disabled. Clients should also gate UI proactively via
 * the core `config.getSystemInfo` capabilities response so users never reach a denied
 * call in the first place.
 */
class PluginNotEnabledException extends LeantimeException
{
    protected int $statusCode = 403;

    protected int $rpcCode = -32004;

    public function __construct(string $message = 'This action requires a plugin that is not enabled.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
