<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Projects\Services\Projects as ProjectsService;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    use DispatchesEvents;

    /**
     * Handle an incoming request
     *
     * @param IncomingRequest                     $request
     * @param \Closure(IncomingRequest): Response $next
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        if (! $request instanceof ApiRequest) {
            return $next($request);
        }

        self::dispatch_event("before_api_request", ['application' => app()]);

        $apiKey = $request->getAPIKey();
        $apiUser = app()->make(ApiService::class)->getAPIKeyUser($apiKey);

        if (! $apiUser) {
            return new Response(json_encode(['error' => 'Invalid API Key']), 401);
        }

        app()->make(AuthService::class)->setUserSession($apiUser);
        app()->make(ProjectsService::class)->setCurrentProject();

        if (! str_starts_with(strtolower(app()->make(Frontcontroller::class)->getCurrentRoute()), 'api.jsonrpc')) {
            return new Response(json_encode(['error' => 'Invalid API endpoint']), 404);
        }

        return $next($request);
    }
}
