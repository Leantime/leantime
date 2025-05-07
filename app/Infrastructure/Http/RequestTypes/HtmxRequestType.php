<?php

namespace Leantime\Infrastructure\Http\RequestTypes;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Http\RequestTypes\RequestTypeInterface;
use Leantime\Infrastructure\Http\HtmxRequest;

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
