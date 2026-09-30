<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Leantime\Core\Support\OutboundUrlGuard;

/**
 * WebhookTransport — the HTTP leg of personal webhook delivery, hardened against SSRF.
 *
 * Checking a URL and then letting the HTTP client resolve the host again leaves a window in which
 * the second DNS answer can point inside the network (DNS rebinding). So the address the SSRF
 * guard validated is pinned onto the connection with CURLOPT_RESOLVE: cURL connects to exactly
 * that address and never resolves the host itself, while the URL — and with it the Host header,
 * TLS SNI and the certificate check — keeps the real hostname. A pin can't redirect a connection
 * that is already open, so no request reuses a pooled connection or leaves one behind.
 *
 * Always https to an endpoint that passes isValidEndpointUrl() — the same rule the profile save
 * applies. Always cURL (a stream handler can't honour the pin), never a proxy (it would resolve
 * the host itself), never a redirect (every 3xx is a failure), TLS verification always on.
 *
 * Throws only fixed-message exceptions and never logs: a webhook URL's path and query usually
 * carry its secret, and Guzzle's own exception messages embed the full URL.
 *
 * Intentionally carries no @api tags: it would let any authenticated JSON-RPC caller make the
 * server POST arbitrary payloads.
 */
class WebhookTransport
{
    /**
     * Upper bound for a webhook URL.
     */
    public const MAX_URL_LENGTH = 2048;

    /**
     * Seconds to wait for the TCP/TLS connection to the endpoint.
     */
    private const CONNECT_TIMEOUT_SECONDS = 2;

    /**
     * Seconds the whole request (connect + send + response) may take.
     */
    private const TOTAL_TIMEOUT_SECONDS = 5;

    private const NOT_ALLOWED_MESSAGE = 'Webhook endpoint is not allowed';

    private Client $httpClient;

    /**
     * @param  CurlHandler  $curlHandler  The only handler requests go through — no stream fallback.
     */
    public function __construct(CurlHandler $curlHandler)
    {
        $this->httpClient = new Client(['handler' => HandlerStack::create($curlHandler)]);
    }

    /**
     * The syntax rule for a webhook endpoint — the one both post() and the profile save
     * (Webhooks::isValidEndpointUrl()) apply, so a URL can only be saved if post() would try it.
     *
     * Requires at most MAX_URL_LENGTH characters, https, no embedded credentials, and a host that
     * is either an IP literal the SSRF guard allows or a plain ASCII hostname of two or more
     * labels whose last label starts with a letter. Never resolves DNS: a hostname's addresses
     * are checked, and pinned, by post() at delivery time.
     *
     * @param  string  $url  The URL to check.
     * @return bool True when the URL is acceptable as a webhook endpoint.
     */
    public static function isValidEndpointUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > self::MAX_URL_LENGTH || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        try {
            // Guzzle's parse, lower-cased host — the exact URL string cURL will be handed.
            $uri = new Uri($url);
        } catch (\InvalidArgumentException) {
            return false;
        }

        // user:pass@host would be sent as basic auth to whoever owns the host.
        if ($uri->getScheme() !== 'https' || $uri->getUserInfo() !== '') {
            return false;
        }

        $host = $uri->getHost();
        if (str_starts_with($host, '[') || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $ip = trim($host, '[]');

            return filter_var($ip, FILTER_VALIDATE_IP) !== false && OutboundUrlGuard::isIpAllowed($ip);
        }

        // The pin only holds if cURL looks up the very name the guard resolved. Refuse spellings it
        // rewrites first ("127.1", "0x7f000001", "2130706433", percent-encoding, IDN), keys
        // differently (trailing dot) or the resolver may expand with search domains (single label).
        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            && preg_match('/\.[a-z][a-z0-9-]*$/', $host) === 1;
    }

    /**
     * POSTs $payload as JSON to $webhookUrl over a new connection, pinned to an address the SSRF
     * guard validated for this call and closed afterwards. Returns only when the endpoint answered 2xx.
     *
     * @param  string  $webhookUrl  The endpoint; must pass isValidEndpointUrl().
     * @param  array<string, mixed>  $payload  The JSON body.
     *
     * @throws \InvalidArgumentException When the endpoint is not allowed: fails isValidEndpointUrl(),
     *                                   unresolvable, or any of its addresses not public.
     * @throws BadResponseException When the endpoint answers anything but 2xx — every 3xx included.
     * @throws ConnectException When the endpoint can't be reached or the request times out.
     * @throws TransferException On any other transfer failure (e.g. TLS).
     */
    public function post(string $webhookUrl, array $payload): void
    {
        if (! self::isValidEndpointUrl($webhookUrl)) {
            throw new \InvalidArgumentException(self::NOT_ALLOWED_MESSAGE);
        }

        // Parses exactly as isValidEndpointUrl() just did, so it cannot throw here.
        $uri = new Uri($webhookUrl);
        $host = $uri->getHost();
        $isIpLiteral = str_starts_with($host, '[') || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;

        $allowedAddresses = OutboundUrlGuard::resolveAllowedAddresses((string) $uri);
        if ($allowedAddresses === []) {
            throw new \InvalidArgumentException(self::NOT_ALLOWED_MESSAGE);
        }

        // The pin only steers new connections: cURL would hand a pooled live connection for this
        // host:port to the request without looking at CURLOPT_RESOLVE, reaching the address checked
        // for an earlier delivery. So every request dials its own connection and closes it after.
        $curlOptions = [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
        ];

        // cURL connects to an IP literal as-is (the guard just classified it), so only names need a pin.
        if (! $isIpLiteral) {
            $port = $uri->getPort() ?? 443; // https only (isValidEndpointUrl)
            $pinnedAddress = str_contains($allowedAddresses[0], ':') ? '['.$allowedAddresses[0].']' : $allowedAddresses[0];
            $curlOptions[CURLOPT_RESOLVE] = [$host.':'.$port.':'.$pinnedAddress];
        }

        $request = new Request('POST', $uri);

        try {
            $response = $this->httpClient->send($request, [
                'allow_redirects' => false,
                'connect_timeout' => self::CONNECT_TIMEOUT_SECONDS,
                'curl' => $curlOptions,
                'http_errors' => false,
                'json' => $payload,
                'proxy' => '', // CURLOPT_PROXY "" — no proxy, and proxy environment variables ignored
                'timeout' => self::TOTAL_TIMEOUT_SECONDS,
                'verify' => true,
            ]);
        } catch (ConnectException) {
            throw new ConnectException('Webhook endpoint could not be reached', $request);
        } catch (GuzzleException) {
            throw new TransferException('Webhook request failed');
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status > 299) {
            throw new BadResponseException('Webhook endpoint answered with a non-2xx status', $request, $response);
        }
    }
}
