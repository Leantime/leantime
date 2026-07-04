<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Shared SSRF guard for server-initiated outbound HTTP requests (external calendars, webhook
 * notifications, and any other feature that fetches a user-supplied URL).
 *
 * Enforces http/https only, resolves every A/AAAA record for the host, and rejects the request
 * when any resolved address is loopback, private, link-local, CGNAT, or otherwise reserved —
 * closing the "public hostname, private IP" bypass. {@see redirectOptions()} re-runs the same
 * check on every redirect hop so an allowed public URL can't 30x-redirect into an internal target.
 */
final class OutboundUrlGuard
{
    /**
     * IPv4 ranges that must never be reached by a server-initiated request. Beyond RFC1918 this
     * adds CGNAT (100.64.0.0/10 — the range the calendar guard was missing), IETF-reserved,
     * benchmarking, multicast, and broadcast ranges.
     *
     * @var array<int, string>
     */
    private const IPV4_DENY_RANGES = [
        '0.0.0.0/8',          // "this" network
        '10.0.0.0/8',         // RFC1918 private
        '100.64.0.0/10',      // CGNAT (RFC6598)
        '127.0.0.0/8',        // loopback
        '169.254.0.0/16',     // link-local (incl. cloud metadata 169.254.169.254)
        '172.16.0.0/12',      // RFC1918 private
        '192.0.0.0/24',       // IETF protocol assignments
        '192.0.2.0/24',       // TEST-NET-1
        '192.168.0.0/16',     // RFC1918 private
        '198.18.0.0/15',      // benchmarking
        '224.0.0.0/4',        // multicast
        '240.0.0.0/4',        // reserved
        '255.255.255.255/32', // broadcast
    ];

    /**
     * True when $url is safe for a server-initiated outbound request.
     */
    public static function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if ($parsed === false || empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        if (! in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            Log::warning('SSRF guard: blocked disallowed scheme', ['scheme' => $parsed['scheme']]);

            return false;
        }

        $host = $parsed['host'];

        // IP literal: validate directly.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return self::isIpAllowed($host);
        }

        // Resolve every A and AAAA record; block if any single record is disallowed.
        $ips = [];
        foreach ((@dns_get_record($host, DNS_A) ?: []) as $record) {
            $ips[] = $record['ip'] ?? null;
        }
        foreach ((@dns_get_record($host, DNS_AAAA) ?: []) as $record) {
            $ips[] = $record['ipv6'] ?? null;
        }
        $ips = array_filter($ips);

        if ($ips === []) {
            Log::warning('SSRF guard: unable to resolve host', ['host' => $host]);

            return false;
        }

        foreach ($ips as $ip) {
            if (! self::isIpAllowed($ip)) {
                Log::warning('SSRF guard: blocked private/reserved IP', ['host' => $host, 'ip' => $ip]);

                return false;
            }
        }

        return true;
    }

    /**
     * True when an IP (v4 or v6) is a public, routable address safe to reach.
     */
    public static function isIpAllowed(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Decompose an IPv4-mapped IPv6 address (::ffff:a.b.c.d) and apply the IPv4 rules,
            // so CGNAT/private ranges can't slip through in v6 form.
            $packed = inet_pton($ip);
            if ($packed !== false && strlen($packed) === 16 && str_starts_with($packed, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff")) {
                $mappedV4 = inet_ntop(substr($packed, 12));

                // Fail closed if the mapped address can't be rendered back to IPv4.
                return $mappedV4 !== false && self::isIpv4Allowed($mappedV4);
            }

            return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return self::isIpv4Allowed($ip);
    }

    private static function isIpv4Allowed(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        foreach (self::IPV4_DENY_RANGES as $range) {
            if (self::ipv4InRange($ip, $range)) {
                return false;
            }
        }

        return true;
    }

    private static function ipv4InRange(string $ip, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    /**
     * Guzzle `allow_redirects` options that re-validate every redirect hop with the same guard,
     * so a permitted public URL can't be used to bounce the request into an internal target.
     *
     * @return array<string, mixed>
     */
    public static function redirectOptions(): array
    {
        return [
            'max' => 5,
            'strict' => true,
            'referer' => false,
            'protocols' => ['http', 'https'],
            'on_redirect' => function (RequestInterface $request, ResponseInterface $response, UriInterface $uri): void {
                if (! self::isAllowedUrl((string) $uri)) {
                    throw new \RuntimeException('SSRF guard: blocked redirect to disallowed URL');
                }
            },
        ];
    }
}
