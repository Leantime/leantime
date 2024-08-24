<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TrustProxies
 *
 * The TrustProxies class is responsible for handling incoming requests and checking if they are from trusted proxies.
 *
 * @package Your\Namespace
 */
class TrustProxies
{
    use DispatchesEvents;

    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies = [];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var string
     */
    protected $headers = IncomingRequest::HEADER_X_FORWARDED_FOR |
                        IncomingRequest::HEADER_X_FORWARDED_HOST |
                        IncomingRequest::HEADER_X_FORWARDED_PORT |
                        IncomingRequest::HEADER_X_FORWARDED_PROTO |
                        IncomingRequest::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Constructor for the class.
     *
     * @param Environment $config An instance of the Environment class.
     */
    public function __construct(Environment $config)
    {

        if (empty($config->trustedProxies)) {
            $config->trustedProxies = "127.0.0.1,REMOTE_ADDR";
        }

        $this->proxies = self::dispatch_filter(
            "trustedProxies",
            explode(",", $config->trustedProxies),
            ['bootloader' => $this]
        );

    }

    /**
     * Handle the incoming request and pass it to the next middleware.
     * If the request is not from a trusted proxy, it returns a response with an error message.
     *
     * @param IncomingRequest $request The incoming request.
     * @param Closure         $next    The next middleware closure.
     * @return Response The response returned by the next middleware.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        $request::setTrustedProxies($this->proxies, $this->headers);

        if (!$request->isFromTrustedProxy()) {
            return new Response(json_encode(['error' => 'Not a trusted proxy']), 403);
        }

        return $next($request);
    }
}
