<?php

namespace Leantime\Core\Http\RequestTypes;

use Leantime\Core\Http\IncomingRequest;

interface RequestTypeInterface
{
    /**
     * Check if the request matches this type
     */
    public function matches(IncomingRequest $request): bool;

    /**
     * Get the priority of this request type
     * Higher numbers mean higher priority
     */
    public function getPriority(): int;

    /**
     * Get the request class to instantiate
     */
    public function getRequestClass(): string;
}
