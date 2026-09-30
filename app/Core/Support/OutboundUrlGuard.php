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
 * when any resolved address is loopback, private, link-local, CGNAT, documentation, multicast, or
 * otherwise reserved — closing the "public hostname, private IP" bypass. IPv6 must be global
 * unicast, and IPv6 forms that carry an IPv4 destination are judged by the IPv4 rules.
 * {@see redirectOptions()} re-runs the same check on every redirect hop so an allowed public URL
 * can't 30x-redirect into an internal target.
 *
 * A check alone still lets the HTTP client resolve the host a second time and get a different
 * answer (DNS rebinding). Callers that need the connection itself pinned to a checked address use
 * {@see resolveAllowedAddresses()} and hand the result to their transport.
 */
final class OutboundUrlGuard
{
    /**
     * IPv4 ranges that must never be reached by a server-initiated request. Beyond RFC1918 this
     * adds CGNAT (100.64.0.0/10 — the range the calendar guard was missing), IETF-reserved,
     * documentation, benchmarking, multicast, and broadcast ranges.
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
        '192.0.2.0/24',       // TEST-NET-1 (documentation)
        '192.168.0.0/16',     // RFC1918 private
        '198.18.0.0/15',      // benchmarking
        '198.51.100.0/24',    // TEST-NET-2 (documentation)
        '203.0.113.0/24',     // TEST-NET-3 (documentation)
        '224.0.0.0/4',        // multicast
        '240.0.0.0/4',        // reserved
        '255.255.255.255/32', // broadcast
    ];

    /**
     * The only IPv6 space a public endpoint can live in. Everything outside it — unspecified,
     * loopback, IPv4-compatible, discard-only, NAT64 local-use, unique-local, site-local,
     * link-local, multicast — is refused without needing its own entry.
     */
    private const IPV6_GLOBAL_UNICAST = '2000::/3';

    /**
     * Parts of global unicast that are still not public endpoints.
     *
     * @var array<int, string>
     */
    private const IPV6_DENY_RANGES = [
        '2001::/23',     // IETF protocol assignments (Teredo 2001::/32 tunnels to an embedded IPv4; benchmarking; ORCHID)
        '2001:db8::/32', // documentation
        '2002::/16',     // 6to4 (tunnels to an embedded IPv4; deprecated by RFC 7526)
        '3fff::/20',     // documentation (RFC 9637)
    ];

    /**
     * 96-bit IPv6 prefixes whose low 32 bits are the IPv4 address the traffic really goes to;
     * that embedded address is judged by the IPv4 rules.
     *
     * @var array<int, string>
     */
    private const IPV6_EMBEDDED_IPV4_PREFIXES = [
        '::ffff:0:0/96', // IPv4-mapped
        '64:ff9b::/96',  // NAT64 well-known prefix (RFC 6052) — what DNS64 answers for IPv4-only hosts
    ];

    /**
     * True when $url is safe for a server-initiated outbound request: http/https, and the host —
     * an IPv4 literal, a bracketed IPv6 literal, or every A/AAAA record of a name — is public.
     *
     * @param  string  $url  The URL to check.
     */
    public static function isAllowedUrl(string $url): bool
    {
        return self::resolveAllowedAddresses($url) !== [];
    }

    /**
     * The addresses a server-initiated request to $url may connect to: the host itself when it is
     * an IP literal (no DNS lookup), otherwise every A and AAAA record of the name. Returns an
     * empty list — never a partial one — when the URL is not http/https, the host can't be
     * resolved, or any single address is not public, since the HTTP client could pick that one.
     *
     * @param  string  $url  The URL to check.
     * @return array<int, string> Unique public addresses, IPv4 before IPv6, IPv6 without brackets;
     *                            empty when the URL is not allowed.
     */
    public static function resolveAllowedAddresses(string $url): array
    {
        $parsed = parse_url($url);

        if ($parsed === false || empty($parsed['scheme']) || empty($parsed['host'])) {
            return [];
        }

        if (! in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            Log::warning('SSRF guard: blocked disallowed scheme', ['scheme' => $parsed['scheme']]);

            return [];
        }

        $host = $parsed['host'];

        // parse_url keeps the brackets of an IPv6 literal ("[2606:4700::1111]"). Brackets may only
        // wrap an IPv6 address (RFC 3986), so unwrap and classify it; anything else in brackets
        // (IPv4, zone ids, IPvFuture) is refused rather than handed to DNS.
        if (str_starts_with($host, '[')) {
            $ipv6 = str_ends_with($host, ']') ? substr($host, 1, -1) : '';
            $isAllowedIpv6 = filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
                && self::isIpAllowed($ipv6);

            return $isAllowedIpv6 ? [$ipv6] : [];
        }

        // IP literal: validate directly.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return self::isIpAllowed($host) ? [$host] : [];
        }

        // Resolve every A and AAAA record; block if any single record is disallowed.
        $ips = [];
        foreach ((@dns_get_record($host, DNS_A) ?: []) as $record) {
            $ips[] = $record['ip'] ?? null;
        }
        foreach ((@dns_get_record($host, DNS_AAAA) ?: []) as $record) {
            $ips[] = $record['ipv6'] ?? null;
        }
        $ips = array_values(array_unique(array_filter($ips)));

        if ($ips === []) {
            Log::warning('SSRF guard: unable to resolve host', ['host' => $host]);

            return [];
        }

        foreach ($ips as $ip) {
            if (! self::isIpAllowed($ip)) {
                Log::warning('SSRF guard: blocked private/reserved IP', ['host' => $host, 'ip' => $ip]);

                return [];
            }
        }

        return $ips;
    }

    /**
     * True when an IP (v4 or v6) is a public, routable address safe to reach.
     */
    public static function isIpAllowed(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return self::isIpv4Allowed($ip);
        }

        $packed = inet_pton($ip);
        if ($packed === false || strlen($packed) !== 16) {
            return false;
        }

        // IPv4-mapped (::ffff:a.b.c.d) and NAT64 (64:ff9b::a.b.c.d) reach the embedded IPv4
        // address, so private/CGNAT/metadata ranges can't slip through in v6 form.
        foreach (self::IPV6_EMBEDDED_IPV4_PREFIXES as $prefix) {
            if (self::ipv6InRange($packed, $prefix)) {
                $embeddedIpv4 = inet_ntop(substr($packed, 12));

                // Fail closed if the embedded address can't be rendered back to IPv4.
                return $embeddedIpv4 !== false && self::isIpv4Allowed($embeddedIpv4);
            }
        }

        if (! self::ipv6InRange($packed, self::IPV6_GLOBAL_UNICAST)) {
            return false;
        }

        foreach (self::IPV6_DENY_RANGES as $range) {
            if (self::ipv6InRange($packed, $range)) {
                return false;
            }
        }

        return true;
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
     * True when a packed (16-byte, inet_pton) IPv6 address lies inside an IPv6 CIDR range.
     *
     * @param  string  $packedIp  The address as returned by inet_pton().
     * @param  string  $range  CIDR notation, e.g. "2001:db8::/32".
     */
    private static function ipv6InRange(string $packedIp, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);
        $packedSubnet = inet_pton($subnet);
        $prefixBits = (int) $bits;

        $wholeBytes = intdiv($prefixBits, 8);
        if (substr($packedIp, 0, $wholeBytes) !== substr($packedSubnet, 0, $wholeBytes)) {
            return false;
        }

        $remainingBits = $prefixBits % 8;
        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($packedIp[$wholeBytes]) & $mask) === (ord($packedSubnet[$wholeBytes]) & $mask);
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
