<?php

namespace Leantime\Infrastructure\Http\RequestTypes;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Http\RequestTypes\RequestTypeInterface;
use Leantime\Infrastructure\Http\ApiRequest;

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
