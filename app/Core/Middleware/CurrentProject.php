<?php

namespace Leantime\Core\Middleware;

use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Core\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;
use Closure;
use Leantime\Domain\Auth\Services\Auth as AuthService;

class CurrentProject
{
    /**
     * Set the current project
     *
     * @param \Closure(IncomingRequest): Response $next
     * @return Response
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        if (app()->make(AuthService::class)->logged_in()) {
            app()->make(ProjectService::class)->setCurrentProject();
        }

        return $next($request);
    }
}
