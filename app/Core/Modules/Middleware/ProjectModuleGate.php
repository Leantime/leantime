<?php

namespace Leantime\Core\Modules\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Modules\ProjectModules;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks pages and HTMX fragments of modules that are switched off in the current project.
 *
 * Runs after CurrentProject, for Laravel routes and convention routes alike, and reads the
 * module and action from the path (/wiki/show, /hx/wiki/…). API and cron requests pass: JSON-RPC
 * is covered by the permission engine, which checks the project the call targets.
 *
 * A blocked page redirects to /modulemanager/notavailable; a blocked HTMX or form request gets
 * a plain 404, since there is nothing the browser could show in its place.
 */
class ProjectModuleGate
{
    public function __construct(private ProjectModules $projectModules) {}

    /**
     * @param  \Closure(IncomingRequest): Response  $next
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        if (! session()->exists('userdata') || $request->isApiOrCronRequest()) {
            return $next($request);
        }

        $segments = $request->segments();

        if (($segments[0] ?? '') === 'hx') {
            array_shift($segments);
        }

        $moduleName = $segments[0] ?? '';
        $actionName = $segments[1] ?? 'index';

        if ($moduleName === '') {
            return $next($request);
        }

        $currentProjectId = (int) session('currentProject');
        $blockingModule = $this->projectModules->blockingRoute($moduleName, $actionName, $currentProjectId);

        if ($blockingModule === null) {
            return $next($request);
        }

        Log::info(sprintf('Module "%s" is off in project %d, blocked %s', $blockingModule->id, $currentProjectId, $request->getPathInfo()));

        if ($request->isHtmxRequest() || ! $request->isMethod('GET')) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return Frontcontroller::redirect(BASE_URL.'/modulemanager/notavailable?module='.urlencode($blockingModule->id));
    }
}
