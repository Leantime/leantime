<?php

namespace Leantime\Core\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP client for server-initiated requests to user-supplied URLs (external calendar feeds,
 * project messenger webhooks), hardened against SSRF.
 *
 * Checking a URL with {@see OutboundUrlGuard} and then letting the HTTP client resolve the host
 * again leaves a window in which the second DNS answer points inside the network (DNS rebinding).
 * So every request is pinned to an address the guard validated for that request with
 * CURLOPT_RESOLVE: cURL connects to exactly that address and never resolves the host itself, while
 * the URL — and with it the Host header, TLS SNI and the certificate check — keeps the real
 * hostname. A pin can't redirect a connection that is already open, so no request reuses a pooled
 * connection or leaves one behind.
 *
 * Always cURL (a stream handler can't honour the pin), never a proxy (it would resolve the host
 * itself), TLS verification always on, and bounded by a connect and a total timeout. Redirects are
 * followed only for GET/HEAD, by this class rather than Guzzle, so every hop is validated and
 * pinned again; any other method treats a 3xx as the final response. A redirect to another
 * origin (scheme, host or port) drops every credential option and header.
 *
 * Throws {@see \InvalidArgumentException} when a URL (or a redirect target) is not allowed, and
 * Guzzle's exceptions for transfer failures.
 *
 * Not a domain service and carries no @api tags: it would let a JSON-RPC caller make the server
 * send arbitrary requests.
 */
class OutboundHttpClient
{
    /**
     * Seconds to wait for the TCP/TLS connection, unless the caller asks for less or more.
     */
    public const DEFAULT_CONNECT_TIMEOUT_SECONDS = 5;

    /**
     * Seconds the whole request may take, unless the caller asks for less or more.
     */
    public const DEFAULT_TOTAL_TIMEOUT_SECONDS = 15;

    /**
     * Redirect hops followed for GET/HEAD before giving up.
     */
    public const MAX_REDIRECTS = 5;

    private const REDIRECT_STATUSES = [301, 302, 303, 307, 308];

    private const NOT_ALLOWED_MESSAGE = 'Outbound request refused: URL failed SSRF safety check';

    private Client $httpClient;

    /**
     * @param  CurlHandler  $curlHandler  The only handler requests go through — no stream fallback.
     */
    public function __construct(CurlHandler $curlHandler)
    {
        $this->httpClient = new Client(['handler' => HandlerStack::create($curlHandler)]);
    }

    /**
     * Whether cURL will look up exactly this host name, so a CURLOPT_RESOLVE pin for it holds.
     *
     * Accepts a plain ASCII hostname of two or more labels whose last label starts with a letter.
     * Refuses spellings cURL rewrites first ("127.1", "0x7f000001", "2130706433", percent-encoding,
     * IDN), keys differently (trailing dot) or the resolver may expand with search domains (single
     * label). IP literals are not host names and are judged by the guard directly.
     *
     * @param  string  $host  The (lower-cased) host as Guzzle's Uri parses it.
     */
    public static function isPinnableHostname(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            && preg_match('/\.[a-z][a-z0-9-]*$/', $host) === 1;
    }

    /**
     * The cURL options that pin a request to $uri onto an address the SSRF guard validated just
     * now, on a fresh connection that is closed afterwards.
     *
     * @param  UriInterface  $uri  The request URI (http or https).
     * @return array<int, mixed>|null The cURL options, or null when the URI is not allowed: not
     *                                http/https, a host name that can't be pinned, unresolvable,
     *                                or any of its addresses not public.
     */
    public static function pinnedCurlOptions(UriInterface $uri): ?array
    {
        $scheme = $uri->getScheme();
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $host = $uri->getHost();
        $isIpLiteral = str_starts_with($host, '[') || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;

        if (! $isIpLiteral && ! self::isPinnableHostname($host)) {
            return null;
        }

        $allowedAddresses = OutboundUrlGuard::resolveAllowedAddresses((string) $uri);
        if ($allowedAddresses === []) {
            return null;
        }

        // The pin only steers new connections: cURL would hand a pooled live connection for this
        // host:port to the request without looking at CURLOPT_RESOLVE, reaching an address checked
        // for an earlier request. So every request dials its own connection and closes it after.
        $curlOptions = [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
        ];

        // cURL connects to an IP literal as-is (the guard just classified it), so only names need a pin.
        if (! $isIpLiteral) {
            $port = $uri->getPort() ?? ($scheme === 'https' ? 443 : 80);
            $pinnedAddress = str_contains($allowedAddresses[0], ':') ? '['.$allowedAddresses[0].']' : $allowedAddresses[0];
            $curlOptions[CURLOPT_RESOLVE] = [$host.':'.$port.':'.$pinnedAddress];
        }

        return $curlOptions;
    }

    /**
     * Sends a GET request (redirects followed, each hop re-validated and re-pinned).
     *
     * @param  string  $url  The user-supplied URL.
     * @param  array<string, mixed>  $options  Guzzle request options (headers, timeouts, ...).
     *
     * @throws \InvalidArgumentException When the URL or a redirect target is not allowed.
     * @throws GuzzleException On transfer failures.
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * Sends a POST request (a 3xx answer is returned as-is, never followed).
     *
     * @param  string  $url  The user-supplied URL.
     * @param  array<string, mixed>  $options  Guzzle request options (body/json, headers, auth, timeouts, ...).
     *
     * @throws \InvalidArgumentException When the URL is not allowed.
     * @throws GuzzleException On transfer failures.
     */
    public function post(string $url, array $options = []): ResponseInterface
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * Sends a request to a user-supplied URL, pinned to a validated address.
     *
     * The caller's options are honoured except the ones that keep the request safe, which always
     * win: the pin (curl), no proxy, TLS verification, and no Guzzle-followed redirects.
     *
     * @param  string  $method  The HTTP method.
     * @param  string  $url  The user-supplied URL.
     * @param  array<string, mixed>  $options  Guzzle request options.
     *
     * @throws \InvalidArgumentException When the URL or a redirect target is not allowed.
     * @throws GuzzleException On transfer failures.
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $method = strtoupper($method);
        $followsRedirects = $method === 'GET' || $method === 'HEAD';

        try {
            $uri = new Uri($url);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException(self::NOT_ALLOWED_MESSAGE);
        }

        for ($hop = 0; ; $hop++) {
            $response = $this->sendPinned($method, $uri, $options);

            if (! $followsRedirects || ! in_array($response->getStatusCode(), self::REDIRECT_STATUSES, true)) {
                return $response;
            }

            $location = $response->getHeaderLine('Location');
            if ($location === '') {
                return $response;
            }

            if ($hop >= self::MAX_REDIRECTS) {
                throw new TooManyRedirectsException('Outbound request exceeded the redirect limit', new Request($method, $uri), $response);
            }

            $nextUri = UriResolver::resolve($uri, new Uri($location));

            // Credentials are for the original origin only; never forward them to another one.
            if (self::origin($nextUri) !== self::origin($uri)) {
                $options = self::withoutCredentials($options);
            }

            $uri = $nextUri;
        }
    }

    /**
     * The origin (scheme, host, effective port) of a URI.
     */
    private static function origin(UriInterface $uri): string
    {
        $port = $uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80);

        return $uri->getScheme().'://'.$uri->getHost().':'.$port;
    }

    /**
     * Request options with every credential-bearing option and header removed.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private static function withoutCredentials(array $options): array
    {
        unset($options['auth'], $options['cookies'], $options['cert'], $options['ssl_key']);

        if (isset($options['headers']) && is_array($options['headers'])) {
            $options['headers'] = array_filter(
                $options['headers'],
                fn ($name) => ! in_array(strtolower((string) $name), ['authorization', 'proxy-authorization', 'cookie'], true),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $options;
    }

    /**
     * Sends one request (no redirect following) pinned to an address validated for it.
     *
     * @param  array<string, mixed>  $options
     *
     * @throws \InvalidArgumentException When the URI is not allowed.
     * @throws GuzzleException On transfer failures.
     */
    private function sendPinned(string $method, UriInterface $uri, array $options): ResponseInterface
    {
        $curlOptions = self::pinnedCurlOptions($uri);
        if ($curlOptions === null) {
            throw new \InvalidArgumentException(self::NOT_ALLOWED_MESSAGE);
        }

        $requestOptions = array_merge(
            [
                'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT_SECONDS,
                'timeout' => self::DEFAULT_TOTAL_TIMEOUT_SECONDS,
            ],
            $options,
            [
                'allow_redirects' => false,
                'curl' => $curlOptions,
                'proxy' => '', // CURLOPT_PROXY "" — no proxy, and proxy environment variables ignored
                'verify' => true,
            ],
        );

        return $this->httpClient->request($method, $uri, $requestOptions);
    }
}
