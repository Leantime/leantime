<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
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

            $actionPath = Frontcontroller::getModuleName();

            //Only change/set project if the request is not htmx, api or cron
            if (!($request instanceof HtmxRequest) && $actionPath != 'api' && $actionPath != 'cron') {
                app()->make(ProjectService::class)->setCurrentProject();
            }
        }

        return $next($request);
    }
}
