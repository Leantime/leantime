<?php

namespace Leantime\Core\Http\RequestType;

use Leantime\Core\Http\IncomingRequest;

class RequestTypeDetector
{
    protected static array $requestTypes = [
        ApiRequestType::class,
        HtmxRequestType::class,
    ];

    /**
     * Register a new request type detector
     */
    public static function register(string $typeClass): void
    {
        if (! in_array($typeClass, self::$requestTypes)) {
            self::$requestTypes[] = $typeClass;
        }
    }

    /**
     * Detect the request type from the incoming request
     */
    public static function detect(IncomingRequest $request): string
    {
        foreach (self::$requestTypes as $typeClass) {
            $type = new $typeClass;
            if ($type->matches($request)) {
                return $type->getRequestClass();
            }
        }

        return IncomingRequest::class;
    }
}
