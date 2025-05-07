<?php

namespace Leantime\Core\Http\RequestTypes;

use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;

class HtmxRequestType implements RequestTypeInterface
{
    public function matches(IncomingRequest $request): bool
    {
        return $request->headers->has('HX-Request');
    }

    public function getPriority(): int
    {
        return 200;
    }

    public function getRequestClass(): string
    {
        return HtmxRequest::class;
    }
}
