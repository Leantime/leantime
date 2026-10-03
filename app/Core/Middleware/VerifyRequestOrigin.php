<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects state-changing requests that a browser sent on behalf of another site.
 *
 * Leantime does not (yet) put a CSRF token on every form, so this middleware protects unsafe
 * methods (POST/PUT/PATCH/DELETE) using the headers browsers attach to every request:
 *
 *  1. `Sec-Fetch-Site` (all current browsers):
 *     - `same-origin` / `none` (user typed the URL, bookmark) are allowed.
 *     - `cross-site` is rejected.
 *     - `same-site` (a sibling subdomain) is only allowed when the `Origin` matches this app.
 *  2. No `Sec-Fetch-Site` (older browsers): the `Origin` header — or `Referer` when `Origin` is
 *     missing — must point at this app's host (request host or the configured app URL).
 *  3. Neither header present: allowed. Browsers always send at least one of them on a cross-site
 *     form post, so a request without any is a non-browser client (curl, mobile app, webhook).
 *
 * Exempt: requests carrying an `x-api-key` or `Authorization: Bearer` header (a browser can
 * only add those cross-site after a CORS preflight, so they can't be forged by a form), and
 * paths that legitimately receive cross-site posts from identity providers. Plugins add paths
 * (relative to the app root, `*` wildcard) through the filter
 * `leantime.core.middleware.verifyrequestorigin.isExemptPath.csrfOriginExemptPaths`.
 *
 * Kill switch: LEAN_CSRF_ORIGIN_CHECK=false.
 */
class VerifyRequestOrigin
{
    use DispatchesEvents;

    /**
     * Paths (relative to the app root, `*` wildcard) that may receive cross-site posts.
     *
     * @var array<int, string>
     */
    protected array $defaultExemptPaths = [
        // OIDC / Socialite / SAML identity-provider callbacks (form_post responses)
        'oidc/callback*',
        'auth/callback*',
        'advancedauth/callback*',
    ];

    public function __construct(protected Environment $config) {}

    /**
     * Handle an incoming request.
     *
     * @param  IncomingRequest  $request  The incoming request.
     * @param  Closure  $next  The next middleware.
     * @return Response 403 when the request originates from another site, otherwise the next response.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        if (! $this->requestIsAllowed($request)) {
            Log::warning('Blocked cross-site request', [
                'method' => $request->getRealMethod(),
                'path' => $request->path(),
                'origin' => $request->headers->get('Origin'),
                'secFetchSite' => $request->headers->get('Sec-Fetch-Site'),
            ]);

            return new Response('Cross-site request blocked.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * Decides whether the request may continue.
     *
     * @param  IncomingRequest  $request  The incoming request.
     * @return bool True when the request is safe, exempt, or verifiably from this app.
     */
    public function requestIsAllowed(IncomingRequest $request): bool
    {
        if ($this->config->get('csrfOriginCheck', true) === false) {
            return true;
        }

        if (! $this->isUnsafeMethod($request)) {
            return true;
        }

        if ($this->isTokenAuthenticated($request)) {
            return true;
        }

        if ($this->isExemptPath($request)) {
            return true;
        }

        $secFetchSite = strtolower((string) $request->headers->get('Sec-Fetch-Site', ''));

        if ($secFetchSite === 'same-origin' || $secFetchSite === 'none') {
            return true;
        }

        if ($secFetchSite === 'cross-site') {
            return false;
        }

        $origin = $request->headers->get('Origin');

        // same-site means a sibling (sub)domain. Only trust it when the origin is this app.
        if ($secFetchSite === 'same-site') {
            return $origin !== null && $this->urlMatchesApp($origin, $request);
        }

        // Unknown or missing Sec-Fetch-Site: fall back to Origin, then Referer.
        if ($origin !== null && $origin !== '') {
            return $this->urlMatchesApp($origin, $request);
        }

        $referer = $request->headers->get('Referer');
        if ($referer !== null && $referer !== '') {
            return $this->urlMatchesApp($referer, $request);
        }

        // No browser provenance headers at all: a non-browser client.
        return true;
    }

    /**
     * Whether the request uses a state-changing method.
     *
     * Checks the real wire method as well as the effective one, so a `_method=GET` override on a
     * POST cannot make a cross-site form post look safe.
     */
    protected function isUnsafeMethod(IncomingRequest $request): bool
    {
        $safeMethods = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

        $realMethod = strtoupper($request->getRealMethod());
        $effectiveMethod = strtoupper($request->getMethod());

        return ! in_array($realMethod, $safeMethods, true) || ! in_array($effectiveMethod, $safeMethods, true);
    }

    /**
     * Whether the request carries API credentials in a header.
     *
     * Custom headers like these force a CORS preflight, which Leantime does not grant to other
     * origins, so a forged cross-site request cannot include them.
     */
    protected function isTokenAuthenticated(IncomingRequest $request): bool
    {
        if (! empty($request->headers->get('x-api-key'))) {
            return true;
        }

        $authorization = (string) $request->headers->get('Authorization', '');

        return Str::startsWith(strtolower(trim($authorization)), 'bearer ');
    }

    /**
     * Whether the request path is on the exemption list (defaults + `csrfOriginExemptPaths` filter).
     */
    protected function isExemptPath(IncomingRequest $request): bool
    {
        $exemptPaths = self::dispatchFilter(
            'csrfOriginExemptPaths',
            $this->defaultExemptPaths,
            ['request' => $request]
        );

        if (! is_array($exemptPaths)) {
            return false;
        }

        $path = strtolower(trim($request->path(), '/'));

        foreach ($exemptPaths as $pattern) {
            if (! is_string($pattern) || $pattern === '') {
                continue;
            }

            if (Str::is(strtolower(trim($pattern, '/')), $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the given URL is an origin of this app (scheme, host and explicit port).
     *
     * Accepts the origin the request was made to (after trusted-proxy resolution) and the origin
     * of the configured app URL (LEAN_APP_URL / BASE_URL), so installs behind a reverse proxy that
     * rewrites the Host header keep working.
     *
     * Schemes must match, with one allowance: an https origin is accepted where the app sees
     * http, because a TLS-terminating proxy without trusted-proxy config makes the app see http
     * while the browser reports https. The reverse (an http page posting to an https app) is
     * rejected — content on the insecure origin must not be able to submit to the secure one.
     *
     * @param  string  $url  An Origin or Referer value.
     */
    protected function urlMatchesApp(string $url, IncomingRequest $request): bool
    {
        $candidate = $this->originOf($url);

        if ($candidate === null) {
            return false;
        }

        $allowedOrigins = [$this->originOf($request->getSchemeAndHttpHost())];

        $appUrls = [(string) $this->config->get('appUrl', '')];
        if (defined('BASE_URL')) {
            $appUrls[] = (string) BASE_URL;
        }

        foreach ($appUrls as $appUrl) {
            $allowedOrigins[] = $appUrl !== '' ? $this->originOf($appUrl) : null;
        }

        foreach ($allowedOrigins as $allowed) {
            if ($allowed === null || $allowed['authority'] !== $candidate['authority']) {
                continue;
            }

            $sameScheme = $allowed['scheme'] === $candidate['scheme'];
            $upgradedScheme = $allowed['scheme'] === 'http' && $candidate['scheme'] === 'https';

            if ($sameScheme || $upgradedScheme) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a URL into its scheme and `host` / `host:port` authority (default ports omitted).
     *
     * @return array{scheme: string, authority: string}|null Null when the URL has no host
     *                                                       (e.g. the literal Origin `null`).
     */
    protected function originOf(string $url): ?array
    {
        $parts = parse_url(trim($url));

        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $scheme = strtolower($parts['scheme'] ?? '');
        $port = $parts['port'] ?? null;

        $defaultPorts = ['http' => 80, 'https' => 443];
        $isDefaultPort = $port === null || ($defaultPorts[$scheme] ?? null) === $port;

        return [
            'scheme' => $scheme,
            'authority' => $isDefaultPort ? $host : $host.':'.$port,
        ];
    }
}
