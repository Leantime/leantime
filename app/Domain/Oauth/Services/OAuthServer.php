<?php

namespace Leantime\Domain\Oauth\Services;

use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Passport\Bridge\Client as BridgeClient;
use Laravel\Passport\Bridge\Scope as BridgeScope;
use Laravel\Passport\Bridge\User as BridgeUser;
use Laravel\Passport\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;
use Leantime\Core\Auth\OAuth\ProtectedResources;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The OAuth 2.1 protocol steps, on top of league/oauth2-server (configured by Passport).
 *
 * Adds what MCP clients need and league doesn't do:
 *  - PKCE is mandatory and must be S256,
 *  - an RFC 8707 `resource` must name one of our protected endpoints (`invalid_target` otherwise),
 *  - every authorization response carries `iss` (RFC 9207; ChatGPT relies on it).
 *
 * The pending authorization request lives in the session between the consent page and the
 * approve/deny POST, guarded by a one-time token (like Passport's own controllers).
 */
class OAuthServer
{
    private const SESSION_AUTH_REQUEST = 'oauth.authRequest';

    private const SESSION_AUTH_TOKEN = 'oauth.authToken';

    private const MAX_REDIRECT_URIS = 5;

    private const NONCE_CACHE_PREFIX = 'oauth.consentNonce.';

    private const NONCE_TTL_SECONDS = 3600;

    /**
     * Redirect URL prefixes every installation accepts at registration: Claude and ChatGPT.
     */
    private const DEFAULT_REDIRECT_PREFIXES = [
        'https://claude.ai/',
        'https://claude.com/',
        'https://chatgpt.com/',
    ];

    public function __construct(
        private AuthorizationServer $authorizationServer,
        private ClientRepository $clients,
        private Environment $config,
        private OAuthTokenStore $tokenStore,
        private CacheRepository $cache,
    ) {}

    /**
     * Issuer identifier of this authorization server: the instance's base URL.
     */
    public function issuer(): string
    {
        return rtrim(BASE_URL, '/');
    }

    /**
     * RFC 8414 authorization server metadata.
     *
     * @return array<string, mixed>
     */
    public function authorizationServerMetadata(): array
    {
        $issuer = $this->issuer();

        return [
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'registration_endpoint' => $issuer.'/oauth/register',
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none'],
            'scopes_supported' => array_keys(ProtectedResources::scopes()),
            'authorization_response_iss_parameter_supported' => true,
        ];
    }

    /**
     * RFC 9728 protected resource metadata for an endpoint path ('mcp'), or for the only protected
     * endpoint when no path is given. Null when the path isn't a protected endpoint.
     *
     * @return array<string, mixed>|null
     */
    public function protectedResourceMetadata(?string $path): ?array
    {
        $path = trim((string) $path, '/');
        $path = $path === '' ? 'mcp' : $path;

        if (ProtectedResources::pathForResource(ProtectedResources::url($path)) === null) {
            return null;
        }

        return [
            'resource' => ProtectedResources::url($path),
            'authorization_servers' => [$this->issuer()],
            'scopes_supported' => [ProtectedResources::scopeFor($path)],
            'bearer_methods_supported' => ['header'],
            'resource_name' => (string) $this->config->get('sitename', 'Leantime'),
        ];
    }

    /**
     * RFC 7591 dynamic client registration. This is how Claude and ChatGPT register themselves.
     *
     * Only public clients (PKCE, no secret) for the authorization code flow are created, and every
     * redirect URL must be on the allowlist ({@see self::isAllowedRedirectUri()}), so a client
     * registered by a stranger can still only send codes to Claude, ChatGPT or the user's own machine.
     *
     * @param  array<string, mixed>  $metadata  The client metadata the client sent.
     * @return array{status: int, body: array<string, mixed>}
     */
    public function registerClient(array $metadata): array
    {
        $redirectUris = $metadata['redirect_uris'] ?? null;

        if (! is_array($redirectUris) || $redirectUris === [] || count($redirectUris) > self::MAX_REDIRECT_URIS) {
            return $this->registrationError('invalid_redirect_uri', 'redirect_uris must list between 1 and '.self::MAX_REDIRECT_URIS.' URLs.');
        }

        foreach ($redirectUris as $redirectUri) {
            if (! is_string($redirectUri) || ! $this->isAllowedRedirectUri($redirectUri)) {
                return $this->registrationError('invalid_redirect_uri', 'This redirect URL is not allowed: '.(is_string($redirectUri) ? $redirectUri : '(not a string)'));
            }
        }

        $grantTypes = $metadata['grant_types'] ?? ['authorization_code'];

        if (! is_array($grantTypes) || array_diff($grantTypes, ['authorization_code', 'refresh_token']) !== []) {
            return $this->registrationError('invalid_client_metadata', 'Only the authorization_code and refresh_token grant types are supported.');
        }

        $authMethod = $metadata['token_endpoint_auth_method'] ?? 'none';

        if ($authMethod !== 'none') {
            return $this->registrationError('invalid_client_metadata', 'Only public clients (token_endpoint_auth_method "none") can register.');
        }

        $clientName = $metadata['client_name'] ?? null;
        $clientName = is_string($clientName) ? trim(mb_substr(strip_tags($clientName), 0, 100)) : '';

        if ($clientName === '') {
            $clientName = (string) (parse_url($redirectUris[0], PHP_URL_HOST) ?: 'MCP client');
        }

        $client = $this->clients->createAuthorizationCodeGrantClient(
            name: $clientName,
            redirectUris: array_values($redirectUris),
            confidential: false,
        );

        return [
            'status' => 201,
            'body' => [
                'client_id' => (string) $client->getKey(),
                'client_id_issued_at' => $client->created_at?->getTimestamp() ?? time(),
                'client_name' => $clientName,
                'redirect_uris' => array_values($redirectUris),
                'grant_types' => ['authorization_code', 'refresh_token'],
                'response_types' => ['code'],
                'token_endpoint_auth_method' => 'none',
                'scope' => implode(' ', array_keys(ProtectedResources::scopes())),
            ],
        ];
    }

    /**
     * Whether a client may register this redirect URL:
     *  - https URLs under an allowed prefix (Claude, ChatGPT, plus LEAN_OAUTH_REDIRECT_DOMAINS),
     *  - http loopback URLs on any port (Claude Code, MCP Inspector and other local clients),
     *  - custom app schemes listed in LEAN_OAUTH_CUSTOM_SCHEMES (e.g. cursor://).
     * Never URLs with credentials or a fragment.
     */
    public function isAllowedRedirectUri(string $redirectUri): bool
    {
        $parts = parse_url($redirectUri);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || preg_match('/[\s\x00-\x1F\x7F]/', $redirectUri)) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);

        if ($scheme === 'http') {
            return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
        }

        if ($scheme === 'https') {
            foreach ($this->allowedRedirectPrefixes() as $allowedPrefix) {
                if (str_starts_with(strtolower($redirectUri), $allowedPrefix)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($scheme, $this->allowedCustomSchemes(), true);
    }

    /**
     * Allowed https redirect prefixes, each ending in "/" so https://claude.ai.evil.com never matches.
     *
     * @return array<int, string>
     */
    private function allowedRedirectPrefixes(): array
    {
        $configuredPrefixes = array_map('trim', explode(',', (string) $this->config->get('oauthRedirectDomains', '')));

        return array_values(array_unique(array_map(
            fn (string $prefix): string => rtrim(strtolower($prefix), '/').'/',
            array_filter(array_merge(self::DEFAULT_REDIRECT_PREFIXES, $configuredPrefixes), fn (string $prefix): bool => str_starts_with(strtolower($prefix), 'https://'))
        )));
    }

    /**
     * @return array<int, string>
     */
    private function allowedCustomSchemes(): array
    {
        $schemes = array_map(fn (string $scheme): string => strtolower(trim($scheme)), explode(',', (string) $this->config->get('oauthCustomSchemes', '')));

        // Never let a "custom" scheme re-open what the rules above restrict.
        return array_values(array_diff(array_filter($schemes), ['http', 'https', 'javascript', 'data', 'file']));
    }

    /**
     * @return array{status: int, body: array<string, string>}
     */
    private function registrationError(string $error, string $description): array
    {
        return ['status' => 400, 'body' => ['error' => $error, 'error_description' => $description]];
    }

    /**
     * Validate an authorization request and park it in the session for the consent screen.
     *
     * Returns the consent screen data, or a Response when the request can't be shown: an error
     * page when the client or redirect URL is invalid (we must not redirect to an unverified URL),
     * otherwise an error redirect back to the client.
     *
     * @return array{clientName: string, redirectHost: string, scopes: array<int, string>, authToken: string}|Response
     */
    public function startAuthorization(IncomingRequest $request, int $userId): array|Response
    {
        try {
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($this->toPsrRequest($request));
        } catch (OAuthServerException $exception) {
            return $this->errorResponse($exception);
        }

        if ($authRequest->getCodeChallengeMethod() !== 'S256') {
            return $this->redirectWithError($authRequest, 'invalid_request', 'PKCE with code_challenge_method S256 is required.');
        }

        $resource = $request->query('resource');

        if ($resource !== null && (! is_string($resource) || ProtectedResources::pathForResource($resource) === null)) {
            return $this->redirectWithError($authRequest, 'invalid_target', 'The requested resource is not served by this Leantime instance.');
        }

        $authRequest->setUser(new BridgeUser((string) $userId));

        $authToken = Str::random(40);
        session([
            self::SESSION_AUTH_REQUEST => serialize($authRequest),
            self::SESSION_AUTH_TOKEN => $authToken,
        ]);

        $clientName = $authRequest->getClient()->getName();
        $redirectUri = (string) $this->redirectUriOf($authRequest);

        return [
            'clientName' => $clientName !== '' ? $clientName : 'Unnamed app',
            'redirectHost' => (string) (parse_url($redirectUri, PHP_URL_HOST) ?: $redirectUri),
            'scopes' => array_map(
                fn (ScopeEntityInterface $scope): string => ProtectedResources::scopes()[$scope->getIdentifier()] ?? $scope->getIdentifier(),
                $authRequest->getScopes()
            ),
            'authToken' => $authToken,
        ];
    }

    /**
     * Finish the parked authorization request: redirect back to the client with a code (approved)
     * or an access_denied error. Null when there is no matching pending request (expired session,
     * replayed or forged form), so the caller can show an error page instead of redirecting.
     */
    public function completeAuthorization(string $postedAuthToken, int $userId, bool $approved): ?Response
    {
        $expectedAuthToken = session()->pull(self::SESSION_AUTH_TOKEN);
        $serializedAuthRequest = session()->pull(self::SESSION_AUTH_REQUEST);

        if (! is_string($expectedAuthToken) || $postedAuthToken === '' || ! hash_equals($expectedAuthToken, $postedAuthToken)) {
            return null;
        }

        if (! is_string($serializedAuthRequest)) {
            return null;
        }

        // The session is loaded per request, so two parallel POSTs of the same form would both
        // find the token there. Claim it in the shared cache too: only the first one gets a code.
        if (! $this->cache->add(self::NONCE_CACHE_PREFIX.hash('sha256', $postedAuthToken), true, self::NONCE_TTL_SECONDS)) {
            return null;
        }

        $authRequest = unserialize($serializedAuthRequest, ['allowed_classes' => [
            AuthorizationRequest::class,
            BridgeClient::class,
            BridgeScope::class,
            BridgeUser::class,
        ]]);

        // The request was parked for the user who saw the consent screen; nobody else may finish it.
        if (! $authRequest instanceof AuthorizationRequestInterface
            || $authRequest->getUser()?->getIdentifier() !== (string) $userId) {
            return null;
        }

        $authRequest->setAuthorizationApproved($approved);

        try {
            $psrResponse = $this->authorizationServer->completeAuthorizationRequest($authRequest, new PsrResponse);
        } catch (OAuthServerException $exception) {
            return $this->errorResponse($exception, $authRequest);
        }

        return $this->withIssuer($this->toResponse($psrResponse));
    }

    /**
     * The token endpoint: exchange an authorization code or a refresh token for tokens.
     */
    public function issueToken(IncomingRequest $request): Response
    {
        $parameters = $request->isJson() ? $request->json()->all() : $request->request->all();
        $resource = $parameters['resource'] ?? null;

        if ($resource !== null && (! is_string($resource) || ProtectedResources::pathForResource($resource) === null)) {
            return $this->jsonError('invalid_target', 'The requested resource is not served by this Leantime instance.', 400);
        }

        $psrRequest = $this->toPsrRequest($request)->withParsedBody($parameters);
        $issueTokens = fn () => $this->toResponse($this->authorizationServer->respondToAccessTokenRequest($psrRequest, new PsrResponse));
        $clientId = $this->clientIdOf($request, $parameters);

        try {
            // Serialize issuance per client (see OAuthTokenStore::withClientLock()): the same code or
            // refresh token can't be redeemed twice in parallel, and Disconnect can't be outrun.
            // Without a client id league rejects the request before issuing anything.
            return $clientId !== null
                ? $this->tokenStore->withClientLock($clientId, $issueTokens)
                : $issueTokens();
        } catch (OAuthServerException $exception) {
            return $this->toResponse($exception->generateHttpResponse(new PsrResponse));
        } catch (\Throwable $exception) {
            Log::error($exception);

            return $this->jsonError('server_error', 'The token could not be issued.', 500);
        }
    }

    /**
     * The client id of a token request, wherever league would read it from: the body, or the user
     * part of an `Authorization: Basic` header (league accepts that for public clients too).
     *
     * @param  array<string, mixed>  $parameters  The token request body.
     */
    private function clientIdOf(IncomingRequest $request, array $parameters): ?string
    {
        $bodyClientId = $parameters['client_id'] ?? null;

        if (is_string($bodyClientId) && trim($bodyClientId) !== '') {
            return trim($bodyClientId);
        }

        $authorization = (string) ($request->headers->get('Authorization') ?? $request->server('HTTP_AUTHORIZATION') ?? $request->server('REDIRECT_HTTP_AUTHORIZATION') ?? '');

        if (! str_starts_with(strtolower($authorization), 'basic ')) {
            return null;
        }

        $credentials = base64_decode(substr($authorization, 6), true);
        $basicClientId = is_string($credentials) ? trim(explode(':', $credentials, 2)[0]) : '';

        return $basicClientId !== '' ? $basicClientId : null;
    }

    /**
     * An OAuth error: a redirect back to the client (with `iss`) when league verified the redirect
     * URL, otherwise a plain error page, since redirecting to an unverified URL would make us an
     * open redirector.
     */
    private function errorResponse(OAuthServerException $exception, ?AuthorizationRequestInterface $authRequest = null): Response
    {
        if ($exception->hasRedirect()) {
            return $this->withIssuer($this->toResponse($exception->generateHttpResponse(new PsrResponse)));
        }

        if ($authRequest !== null && $this->redirectUriOf($authRequest) !== null) {
            return $this->redirectWithError($authRequest, $exception->getErrorType(), $exception->getMessage());
        }

        return new Response(
            e($exception->getMessage()).($exception->getHint() ? ' '.e($exception->getHint()) : ''),
            $exception->getHttpStatusCode(),
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    /**
     * Redirect to the client's (already verified) redirect URL with an error, the state and `iss`.
     */
    private function redirectWithError(AuthorizationRequestInterface $authRequest, string $error, string $description): Response
    {
        $parameters = array_filter([
            'error' => $error,
            'error_description' => $description,
            'state' => $authRequest->getState(),
        ], fn ($value) => $value !== null && $value !== '');

        return $this->withIssuer(new Response('', 302, [
            'Location' => $this->appendQuery((string) $this->redirectUriOf($authRequest), $parameters),
        ]));
    }

    /**
     * Where the authorization response goes: the request's redirect_uri, or, when the client
     * omitted it, the client's only registered redirect URL (the one league will use). The
     * request itself is left as-is, so the token request doesn't gain a redirect_uri requirement.
     */
    private function redirectUriOf(AuthorizationRequestInterface $authRequest): ?string
    {
        $redirectUri = $authRequest->getRedirectUri();

        if (is_string($redirectUri) && $redirectUri !== '') {
            return $redirectUri;
        }

        $registeredRedirectUris = (array) $authRequest->getClient()->getRedirectUri();

        return count($registeredRedirectUris) === 1 ? (string) reset($registeredRedirectUris) : null;
    }

    /**
     * RFC 9207: add `iss` to an authorization response redirect.
     */
    private function withIssuer(Response $response): Response
    {
        $location = $response->headers->get('Location');

        if ($location !== null && $location !== '') {
            $response->headers->set('Location', $this->appendQuery($location, ['iss' => $this->issuer()]));
        }

        return $response;
    }

    /**
     * @param  array<string, string>  $parameters
     */
    private function appendQuery(string $url, array $parameters): string
    {
        $fragment = '';

        if (($hashPosition = strpos($url, '#')) !== false) {
            $fragment = substr($url, $hashPosition);
            $url = substr($url, 0, $hashPosition);
        }

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($parameters, '', '&', PHP_QUERY_RFC3986).$fragment;
    }

    private function jsonError(string $error, string $description, int $status): Response
    {
        return new Response(
            (string) json_encode(['error' => $error, 'error_description' => $description]),
            $status,
            ['Content-Type' => 'application/json', 'Cache-Control' => 'no-store']
        );
    }

    /**
     * Build the PSR-7 request league expects. Headers are copied explicitly (Apache hides
     * Authorization from PHP's globals here, see ApiRequest::getBearerToken()).
     */
    private function toPsrRequest(IncomingRequest $request): ServerRequest
    {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $headers[$name] = $values;
        }

        if (method_exists($request, 'getBearerToken') && ($bearer = $request->getBearerToken()) !== null && $bearer !== '') {
            $headers['authorization'] = ['Bearer '.$bearer];
        }

        return (new ServerRequest($request->getMethod(), $request->getUri(), $headers))
            ->withQueryParams($request->query->all());
    }

    private function toResponse(ResponseInterface $psrResponse): Response
    {
        $response = new Response((string) $psrResponse->getBody(), $psrResponse->getStatusCode());

        foreach ($psrResponse->getHeaders() as $name => $values) {
            $response->headers->set($name, $values);
        }

        return $response;
    }
}
