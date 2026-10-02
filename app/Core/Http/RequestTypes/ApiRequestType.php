<?php

namespace Leantime\Core\Http\RequestTypes;

use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;

class ApiRequestType implements RequestTypeInterface
{
    /**
     * An API request carries an API key or Bearer token, or targets an API path. The path check
     * uses the normalized path ({@see IncomingRequest::isApiRequest()}), never the raw URI.
     */
    public function matches(IncomingRequest $request): bool
    {
        return
            $request->headers->has('x-api-key')
            || $request->bearerToken()
            || $request->isApiRequest();
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
