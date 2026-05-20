<?php

namespace Leantime\Domain\Mcp\Controllers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Mcp\Services\McpServer;
use Symfony\Component\HttpFoundation\Response;

class Mcp
{
    public function __construct(private McpServer $server) {}

    public function __invoke(IncomingRequest $request): Response
    {
        return $this->server->handle($request);
    }
}
