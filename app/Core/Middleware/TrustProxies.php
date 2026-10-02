<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TrustProxies
 *
 * The TrustProxies class is responsible for handling incoming requests and checking if they are from trusted proxies.
 */
class TrustProxies
{
    /**
     * Proxies trusted when LEAN_TRUSTED_PROXIES is not set: loopback and private networks
     * (Docker networks, LAN reverse proxies, cloud load balancers inside a VPC). A client on the
     * public internet can therefore not spoof its IP / scheme / host through X-Forwarded-* headers.
     */
    public const DEFAULT_TRUSTED_PROXIES = 'PRIVATE_SUBNETS';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = IncomingRequest::HEADER_X_FORWARDED_FOR |
        IncomingRequest::HEADER_X_FORWARDED_HOST |
        IncomingRequest::HEADER_X_FORWARDED_PORT |
        IncomingRequest::HEADER_X_FORWARDED_PROTO |
        IncomingRequest::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Whether the admin configured an explicit proxy list (which also acts as an access allowlist).
     */
    private bool $restrictToTrustedProxies;

    /**
     * Constructor for the class.
     *
     * @param  Environment  $config  An instance of the Environment class.
     */
    public function __construct(Environment $config)
    {
        $this->restrictToTrustedProxies = trim((string) $config->trustedProxies) !== '';
    }

    /**
     * Resolve the configured proxy list (LEAN_TRUSTED_PROXIES) to the list handed to
     * Request::setTrustedProxies(). Empty config falls back to {@see self::DEFAULT_TRUSTED_PROXIES}.
     *
     * @return array<int, string>
     */
    public static function resolveTrustedProxies(?string $configured): array
    {
        $configured = trim((string) $configured);

        if ($configured === '') {
            $configured = self::DEFAULT_TRUSTED_PROXIES;
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $configured)),
            fn (string $proxy) => $proxy !== ''
        ));
    }

    /**
     * Handle the incoming request and pass it to the next middleware.
     *
     * With an explicitly configured proxy list, requests that did not come through one of those
     * proxies are rejected. With the default list no request is rejected; it only controls whose
     * forwarding headers are believed.
     *
     * @param  IncomingRequest  $request  The incoming request.
     * @param  Closure  $next  The next middleware closure.
     * @return Response The response returned by the next middleware.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        // Trusted proxies config is set in LoadConfig
        if ($this->restrictToTrustedProxies && ! $request->isFromTrustedProxy()) {
            return new Response(json_encode(['error' => 'Not a trusted proxy']), 403);
        }

        return $next($request);
    }
}
