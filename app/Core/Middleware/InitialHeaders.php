<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Eventhelpers;
use Leantime\Core\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

class InitialHeaders
{
    use Eventhelpers;

    /**
     * Set up the initial headers
     *
     * @param \Closure(IncomingRequest): Response $next
     * @throws BindingResolutionException
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (
            self::dispatch_filter('headers', [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Access-Control-Allow-Origin' => BASE_URL,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache'
            ]) as $key => $value
        ) {
            if ($response->headers->has($key)) {
                continue;
            }

            $response->headers->set($key, $value);
        }

        return $response;
    }
}
