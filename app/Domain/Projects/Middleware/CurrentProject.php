<?php

namespace Leantime\Domain\Projects\Middleware;

use Closure;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class CurrentProject
{
    /**
     * Set the current project
     *
     * @param  \Closure(IncomingRequest): Response  $next
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        if (app()->make(AuthService::class)->loggedIn()) {

            $actionPath = $request->getModuleName();

            // Only change/set project if the request is not htmx, api or cron
            if (! ($request instanceof HtmxRequest) && $actionPath != 'api' && $actionPath != 'cron') {
                app()->make(ProjectService::class)->setCurrentProject();

                // Ensure the user has a default project on first login.
                // This was previously triggered inside a view composer (Helpermodal)
                // which is unsafe since view composers should never perform writes.
                app()->make(Helper::class)->ensureDefaultProject(
                    session('userdata.id'),
                    session('userdata.role') ?? 'editor'
                );
            }
        }

        return $next($request);
    }
}
