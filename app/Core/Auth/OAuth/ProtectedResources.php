<?php

namespace Leantime\Core\Auth\OAuth;

use Laravel\Mcp\Server\Registrar;
use Leantime\Core\Http\IncomingRequest;

/**
 * The endpoints that accept OAuth access tokens, and the scope each one requires.
 *
 * MCP is the only one today. An OAuth token is accepted on an endpoint only when that endpoint is
 * listed here AND the token carries the endpoint's scope, so tokens issued for MCP can never be
 * replayed against /api/jsonrpc. Opening another endpoint to OAuth later means adding a row here
 * (plus its scope description in the language files), not touching AuthCheck.
 */
final class ProtectedResources
{
    /**
     * First path segment of a protected endpoint => the scope a token needs to use it.
     *
     * @var array<string, string>
     */
    private const RESOURCES = [
        'mcp' => Registrar::OAUTH_SCOPE,
    ];

    /**
     * Every scope this server can grant, keyed by id, with the language key that describes it on
     * the consent screen. Passport only accepts scopes registered here.
     *
     * @return array<string, string>
     */
    public static function scopes(): array
    {
        return [
            Registrar::OAUTH_SCOPE => 'oauth.scope.mcp',
        ];
    }

    /**
     * The protected endpoint path ('mcp') the request targets, or null when it targets none.
     */
    public static function pathForRequest(IncomingRequest $request): ?string
    {
        $firstSegment = $request->normalizedSegments()[0] ?? '';

        return array_key_exists($firstSegment, self::RESOURCES) ? $firstSegment : null;
    }

    /**
     * The scope a token needs for the given protected endpoint path.
     */
    public static function scopeFor(string $path): string
    {
        return self::RESOURCES[$path];
    }

    /**
     * Canonical resource URL of a protected endpoint (RFC 8707 / RFC 9728), e.g. https://host/mcp.
     */
    public static function url(string $path): string
    {
        return rtrim(BASE_URL, '/').'/'.$path;
    }

    /**
     * Resolve an RFC 8707 `resource` value sent by a client to one of our protected endpoint
     * paths, or null when it names something this server doesn't protect.
     *
     * Clients send the URL the user typed, so a trailing slash, a different case in the scheme/host
     * or an explicit default port must still match. Query strings and fragments never match.
     */
    public static function pathForResource(string $resource): ?string
    {
        $resource = trim($resource);

        if ($resource === '' || str_contains($resource, '#') || str_contains($resource, '?')) {
            return null;
        }

        $normalizedResource = self::normalizeUrl($resource);

        if ($normalizedResource === null) {
            return null;
        }

        foreach (array_keys(self::RESOURCES) as $path) {
            if ($normalizedResource === self::normalizeUrl(self::url($path))) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Lowercase scheme + host, drop default ports and trailing slashes, keep the path as-is.
     */
    private static function normalizeUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $port = $parts['port'] ?? null;

        if (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) {
            $port = null;
        }

        $path = rtrim($parts['path'] ?? '', '/');

        return $scheme.'://'.$host.($port !== null ? ':'.$port : '').$path;
    }
}
