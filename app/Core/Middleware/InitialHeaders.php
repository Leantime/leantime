<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

class InitialHeaders
{
    use DispatchesEvents;

    /**
     * Set up the initial headers
     *
     * @param  \Closure(IncomingRequest): Response  $next
     *
     * @throws BindingResolutionException
     **/
    public function handle($request, Closure $next): Response
    {

        $response = $next($request);

        // Content Security Policy
        $cspParts = [
            "default-src 'self' 'unsafe-inline'",
            "base-uri 'self';",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' unpkg.com",
            "font-src 'self'  data: unpkg.com",
            "img-src * 'self' *.leantime.io *.amazonaws.com data: blob: marketplace.localhost",
            "frame-src 'self' *.google.com *.microsoft.com *.live.com",
            "frame-ancestors 'self' *.google.com *.microsoft.com *.live.com",
            "connect-src 'self' https://st-api.pearshadow.com http://host.docker.internal:3300 https://*.ngrok-free.app http://localhost:3300" . trim((string) (config('app.sihterice_address') ?? '')),
        ];
        $cspParts = self::dispatchFilter('cspParts', $cspParts);
        $csp = implode(';', $cspParts);

        foreach (
            self::dispatchFilter('headers', [
                'X-Frame-Options' => 'SAMEORIGIN',
                'X-XSS-Protection' => '1; mode=block',
                'X-Content-Type-Options' => 'nosniff',
                'Referrer-Policy', 'same-origin',
                'Access-Control-Allow-Origin' => BASE_URL,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Content-Security-Policy' => $csp,
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
