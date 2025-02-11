<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\Http\RequestType\ApiRequestType;
use Leantime\Core\Http\RequestType\HtmxRequestType;
use Leantime\Core\Http\RequestType\RequestTypeDetector;

class RequestTypes extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RequestTypeDetector::class);
    }

    public function boot(): void
    {
        $detector = $this->app->make(RequestTypeDetector::class);

        $detector->register(new ApiRequestType());
        $detector->register(new HtmxRequestType());
    }
}
