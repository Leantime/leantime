<?php

namespace Leantime\Core\Http\RequestTypes;

use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;

class ApiRequestType implements RequestTypeInterface
{
    public function matches(IncomingRequest $request): bool
    {
        $requestUri = strtolower($request->getRequestUri());

        return $request->headers->has('x-api-key') ||
               str_starts_with($requestUri, '/api/jsonrpc');
    }

    public function getPriority(): int
    {
        return 300; // Higher priority than HTMX
    }

    public function getRequestClass(): string
    {
        return ApiRequest::class;
    }
}
