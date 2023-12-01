<?php

namespace Leantime\Core\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use Closure;
use Leantime\Core\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Reports\Services\Reports as ReportService;

class Telemetry
{
    protected PromiseInterface $telemetryResponse;

    /**
     * Handle an incoming request
     *
     * @param \Closure(IncomingRequest): Response $next
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        $this->telemetryResponse = app()->make(ReportService::class)->sendAnonymousTelemetry();

        return $next($request);
    }

    /**
     * Terminate the request
     *
     * @param IncomingRequest $request
     * @param Response $response
     * @return void
     * @throws BindingResolutionException
     **/
    public function terminate(IncomingRequest $request, Response $response): void
    {
        if (! isset($this->telemetryResponse) || ! $this->telemetryResponse) {
            return;
        }

        try {
            $this->telemetryResponse->wait();
        } catch (\Throwable $e) {
            error_log($e);
        }
    }
}
