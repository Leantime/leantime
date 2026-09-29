<?php

namespace Unit\app\Core\Support;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Leantime\Core\Support\OutboundUrlGuard;
use Unit\TestCase;

/**
 * Covers the SSRF guard's address classification and redirect re-validation using IP literals and
 * direct calls, so nothing here depends on live DNS or the network.
 */
class OutboundUrlGuardTest extends TestCase
{
    /**
     * @dataProvider ipProvider
     */
    public function test_is_ip_allowed(string $ip, bool $expected): void
    {
        $this->assertSame($expected, OutboundUrlGuard::isIpAllowed($ip));
    }

    public static function ipProvider(): array
    {
        return [
            'loopback v4' => ['127.0.0.1', false],
            'private 10/8' => ['10.1.2.3', false],
            'private 172.16/12' => ['172.16.5.5', false],
            'private 192.168/16' => ['192.168.1.1', false],
            'cgnat 100.64/10' => ['100.64.0.1', false],
            'link-local metadata' => ['169.254.169.254', false],
            'reserved 0.0.0.0/8' => ['0.0.0.0', false],
            'public v4 (google dns)' => ['8.8.8.8', true],
            'public v4 (cloudflare)' => ['1.1.1.1', true],
            'loopback v6' => ['::1', false],
            'public v6 (cloudflare)' => ['2606:4700:4700::1111', true],
            'ipv4-mapped loopback' => ['::ffff:127.0.0.1', false],
            'ipv4-mapped cgnat' => ['::ffff:100.64.0.1', false],
            'ipv4-mapped public' => ['::ffff:8.8.8.8', true],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function test_is_allowed_url(string $url, bool $expected): void
    {
        $this->assertSame($expected, OutboundUrlGuard::isAllowedUrl($url));
    }

    public static function urlProvider(): array
    {
        return [
            'loopback literal' => ['http://127.0.0.1/feed.ics', false],
            'cgnat literal' => ['http://100.64.0.1/', false],
            'metadata literal' => ['http://169.254.169.254/latest/meta-data/', false],
            'public literal' => ['https://8.8.8.8/', true],
            'public ipv6 literal' => ['https://[2606:4700:4700::1111]/hook', true],
            'public ipv6 literal with port' => ['https://[2001:4860:4860::8888]:8443/hook', true],
            'loopback ipv6 literal' => ['https://[::1]/hook', false],
            'unspecified ipv6 literal' => ['https://[::]/', false],
            'unique-local ipv6 literal' => ['https://[fd12:3456::1]/hook', false],
            'link-local ipv6 literal' => ['https://[fe80::1]/hook', false],
            'ipv4-mapped loopback literal' => ['https://[::ffff:127.0.0.1]/hook', false],
            'ipv4-mapped private literal' => ['https://[::ffff:10.0.0.1]/hook', false],
            'bracketed ipv4' => ['https://[8.8.8.8]/hook', false],
            'ipv6 zone id' => ['https://[fe80::1%25eth0]/hook', false],
            'non-http scheme' => ['ftp://8.8.8.8/', false],
            'file scheme' => ['file:///etc/passwd', false],
            'garbage' => ['not-a-url', false],
        ];
    }

    public function test_redirect_options_block_disallowed_hop(): void
    {
        $onRedirect = OutboundUrlGuard::redirectOptions()['on_redirect'];

        $this->expectException(\RuntimeException::class);

        $onRedirect(new Request('GET', 'https://8.8.8.8/'), new Response(302), new Uri('http://169.254.169.254/'));
    }

    public function test_redirect_options_block_ipv6_loopback_hop(): void
    {
        $onRedirect = OutboundUrlGuard::redirectOptions()['on_redirect'];

        $this->expectException(\RuntimeException::class);

        $onRedirect(new Request('GET', 'https://[2606:4700:4700::1111]/'), new Response(302), new Uri('https://[::1]/'));
    }

    public function test_redirect_options_allow_public_hop(): void
    {
        $onRedirect = OutboundUrlGuard::redirectOptions()['on_redirect'];

        // A public → public redirect must not throw, including to an IPv6 literal.
        $onRedirect(new Request('GET', 'https://8.8.8.8/'), new Response(302), new Uri('https://1.1.1.1/'));
        $onRedirect(new Request('GET', 'https://8.8.8.8/'), new Response(302), new Uri('https://[2606:4700:4700::1111]/'));

        $this->assertTrue(true);
    }
}
