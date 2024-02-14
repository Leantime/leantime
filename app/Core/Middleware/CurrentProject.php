<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

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
        if (app()->make(AuthService::class)->loggedIn()) {
            app()->make(ProjectService::class)->setCurrentProject();
        }

        return $next($request);
    }
}
