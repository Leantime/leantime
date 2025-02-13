<?php

namespace Leantime\Core\Http\RequestType;

use Illuminate\Support\Collection;
use Leantime\Core\Http\IncomingRequest;

class RequestTypeDetector
{
    /**
     * @var Collection<RequestTypeInterface>
     */
    protected Collection $requestTypes;

    public function __construct()
    {
        $this->requestTypes = collect([
            new ApiRequestType,
            new HtmxRequestType,
        ]);
    }

    /**
     * Register a new request type detector
     */
    public function register(RequestTypeInterface $type): void
    {
        $this->requestTypes->push($type);
    }

    /**
     * Detect the request type from the incoming request
     */
    public function detect(IncomingRequest $request): string
    {

        $matchedType = $this->requestTypes
            ->sort(fn (RequestTypeInterface $a, RequestTypeInterface $b) => $b->getPriority() <=> $a->getPriority())
            ->first(fn (RequestTypeInterface $type) => $type->matches($request));

        $requestClass = $matchedType ? $matchedType->getRequestClass() : IncomingRequest::class;

        return $requestClass;
    }
}
