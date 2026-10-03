<?php

namespace Leantime\Core\Support {
    if (! function_exists(__NAMESPACE__.'\dns_get_record')) {
        /**
         * Test double for the global dns_get_record() as called from OutboundUrlGuard. While a test
         * has installed $GLOBALS['leantimeTestDnsResolver'] every lookup is answered by it instead of
         * the network; otherwise it defers to the real function. Declared identically by each test
         * that fakes DNS — whichever file loads first defines it.
         */
        function dns_get_record(string $hostname, int $type = DNS_ANY): array|false
        {
            $resolver = $GLOBALS['leantimeTestDnsResolver'] ?? null;

            return $resolver !== null ? $resolver($hostname, $type) : \dns_get_record($hostname, $type);
        }
    }
}

namespace Unit\app\Core\Support {

    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use GuzzleHttp\Psr7\Uri;
    use Illuminate\Support\Facades\Log;
    use Leantime\Core\Support\OutboundUrlGuard;
    use Unit\TestCase;

    /**
     * Covers the SSRF guard's address classification, DNS resolution and redirect re-validation.
     * Hostnames are answered by a fake resolver (see the namespaced dns_get_record above), so
     * nothing here depends on live DNS or the network.
     */
    class OutboundUrlGuardTest extends TestCase
    {
        /**
         * Hostnames the fake resolver was asked about, in order.
         *
         * @var array<int, string>
         */
        private array $dnsLookups = [];

        protected function tearDown(): void
        {
            unset($GLOBALS['leantimeTestDnsResolver']);

            parent::tearDown();
        }

        /**
         * Answers DNS from $records instead of the network. Unknown hosts have no records.
         *
         * @param  array<string, array{A?: array<int, string>, AAAA?: array<int, string>}>  $records
         */
        private function fakeDns(array $records): void
        {
            $this->dnsLookups = [];
            $GLOBALS['leantimeTestDnsResolver'] = function (string $hostname, int $type) use ($records): array {
                $this->dnsLookups[] = $hostname;

                if ($type === DNS_A) {
                    return array_map(fn (string $ip) => ['host' => $hostname, 'type' => 'A', 'ip' => $ip], $records[$hostname]['A'] ?? []);
                }

                if ($type === DNS_AAAA) {
                    return array_map(fn (string $ip) => ['host' => $hostname, 'type' => 'AAAA', 'ipv6' => $ip], $records[$hostname]['AAAA'] ?? []);
                }

                return [];
            };
        }

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
                'documentation v4 TEST-NET-1' => ['192.0.2.10', false],
                'documentation v4 TEST-NET-2' => ['198.51.100.7', false],
                'documentation v4 TEST-NET-3' => ['203.0.113.9', false],
                'public v4 next to TEST-NET-2' => ['198.51.101.1', true],
                'public v4 next to TEST-NET-3' => ['203.0.114.1', true],
                'public v4 (google dns)' => ['8.8.8.8', true],
                'public v4 (cloudflare)' => ['1.1.1.1', true],
                'loopback v6' => ['::1', false],
                'unspecified v6' => ['::', false],
                'public v6 (cloudflare)' => ['2606:4700:4700::1111', true],
                'public v6 (google)' => ['2001:4860:4860::8888', true],
                'multicast v6 link-local ff02' => ['ff02::1', false],
                'multicast v6 global ff0e' => ['ff0e::1', false],
                'documentation v6 2001:db8' => ['2001:db8::1', false],
                'documentation v6 3fff::/20' => ['3fff::1', false],
                'unique-local v6' => ['fd12:3456::1', false],
                'link-local v6' => ['fe80::1', false],
                'site-local v6 (deprecated)' => ['fec0::1', false],
                'discard-only v6 100::/64' => ['100::1', false],
                'ipv4-compatible loopback' => ['::127.0.0.1', false],
                'teredo 2001::/32' => ['2001::1', false],
                'ietf benchmarking v6' => ['2001:2::1', false],
                '6to4 of a private v4' => ['2002:a00:1::1', false],
                '6to4 of a public v4' => ['2002:808:808::1', false],
                'nat64 of a private v4' => ['64:ff9b::a00:1', false],
                'nat64 of the metadata v4' => ['64:ff9b::169.254.169.254', false],
                'nat64 of a public v4' => ['64:ff9b::8.8.8.8', true],
                'nat64 local-use prefix' => ['64:ff9b:1::a00:1', false],
                'ipv4-mapped loopback' => ['::ffff:127.0.0.1', false],
                'ipv4-mapped cgnat' => ['::ffff:100.64.0.1', false],
                'ipv4-mapped public' => ['::ffff:8.8.8.8', true],
                'zone id' => ['fe80::1%eth0', false],
                'not an ip' => ['example.com', false],
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
                'multicast ipv6 literal' => ['https://[ff02::1]/hook', false],
                'documentation ipv6 literal' => ['https://[2001:db8::1]/hook', false],
                'nat64 private ipv6 literal' => ['https://[64:ff9b::a00:1]/hook', false],
                'ipv4-mapped loopback literal' => ['https://[::ffff:127.0.0.1]/hook', false],
                'ipv4-mapped private literal' => ['https://[::ffff:10.0.0.1]/hook', false],
                'bracketed ipv4' => ['https://[8.8.8.8]/hook', false],
                'ipv6 zone id' => ['https://[fe80::1%25eth0]/hook', false],
                'non-http scheme' => ['ftp://8.8.8.8/', false],
                'file scheme' => ['file:///etc/passwd', false],
                'garbage' => ['not-a-url', false],
            ];
        }

        public function test_resolve_allowed_addresses_returns_every_public_address_of_a_host(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => ['93.184.216.34', '93.184.216.35'], 'AAAA' => ['2606:2800:220:1::248']]]);

            $this->assertSame(
                ['93.184.216.34', '93.184.216.35', '2606:2800:220:1::248'],
                OutboundUrlGuard::resolveAllowedAddresses('https://hooks.example.test/hook?token=abc')
            );
            $this->assertTrue(OutboundUrlGuard::isAllowedUrl('https://hooks.example.test/hook?token=abc'));
        }

        public function test_resolve_allowed_addresses_returns_a_repeated_record_once(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => ['93.184.216.34', '93.184.216.34']]]);

            $this->assertSame(['93.184.216.34'], OutboundUrlGuard::resolveAllowedAddresses('https://hooks.example.test/'));
        }

        /**
         * One non-public record is enough to refuse the whole host: the HTTP client could pick it.
         *
         * @dataProvider mixedRecordsProvider
         */
        public function test_resolve_allowed_addresses_refuses_a_host_with_any_non_public_record(array $records): void
        {
            $this->fakeDns(['mixed.example.test' => $records]);

            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses('https://mixed.example.test/hook'));
            $this->assertFalse(OutboundUrlGuard::isAllowedUrl('https://mixed.example.test/hook'));
        }

        public static function mixedRecordsProvider(): array
        {
            return [
                'public and private A' => [['A' => ['93.184.216.34', '10.0.0.5']]],
                'private A listed first' => [['A' => ['127.0.0.1', '93.184.216.34']]],
                'public A, metadata A' => [['A' => ['93.184.216.34', '169.254.169.254']]],
                'public A, unique-local AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['fd00::1']]],
                'public A, loopback AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['::1']]],
                'public A, ipv4-mapped metadata AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['::ffff:169.254.169.254']]],
                'public A, nat64 private AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['64:ff9b::a00:5']]],
                'public A, multicast AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['ff02::1']]],
                'public AAAA, documentation A' => [['A' => ['203.0.113.5'], 'AAAA' => ['2606:2800:220:1::248']]],
            ];
        }

        public function test_resolve_allowed_addresses_refuses_an_unresolvable_host(): void
        {
            $this->fakeDns([]);

            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses('https://nowhere.example.test/hook'));
        }

        public function test_resolve_allowed_addresses_returns_ip_literals_without_a_dns_lookup(): void
        {
            $this->fakeDns([]);

            $this->assertSame(['8.8.8.8'], OutboundUrlGuard::resolveAllowedAddresses('https://8.8.8.8/hook'));
            $this->assertSame(['2606:4700:4700::1111'], OutboundUrlGuard::resolveAllowedAddresses('https://[2606:4700:4700::1111]:8443/hook'));
            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses('https://10.0.0.1/hook'));
            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses('https://[ff02::1]/hook'));
            $this->assertSame([], $this->dnsLookups, 'IP literals are classified directly, never looked up');
        }

        public function test_resolve_allowed_addresses_refuses_non_http_urls_without_a_dns_lookup(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => ['93.184.216.34']]]);

            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses('ftp://hooks.example.test/hook'));
            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses('not-a-url'));
            $this->assertSame([], $this->dnsLookups);
        }

        /**
         * Every part of a user-supplied URL can carry a secret — a webhook's per-user token often
         * sits in its hostname — so a refusal is logged by its reason alone: never the scheme, the
         * host or an address the host resolved to.
         *
         * @dataProvider refusedUrlProvider
         */
        public function test_refusals_are_logged_without_any_part_of_the_url(string $url, array $records): void
        {
            $logged = [];
            foreach (['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'] as $level) {
                Log::shouldReceive($level)->andReturnUsing(function ($message, $context = []) use (&$logged) {
                    $logged[] = $message.' '.json_encode($context, JSON_UNESCAPED_SLASHES);
                });
            }
            $this->fakeDns(['secret-token.hooks.example.test' => $records]);

            $this->assertSame([], OutboundUrlGuard::resolveAllowedAddresses($url));

            $this->assertCount(1, $logged, 'The refusal is logged once');
            foreach (['secret-token', 'hooks.example.test', '93.184.216.34', '10.0.0.5', '/endpoint'] as $partOfTheUrl) {
                $this->assertStringNotContainsString($partOfTheUrl, $logged[0]);
            }
        }

        public static function refusedUrlProvider(): array
        {
            return [
                'host that does not resolve' => ['https://secret-token.hooks.example.test/endpoint', []],
                'host with a public and a private address' => ['https://secret-token.hooks.example.test/endpoint', ['A' => ['93.184.216.34', '10.0.0.5']]],
                'disallowed scheme' => ['secret-token://hooks.example.test/endpoint', []],
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

        public function test_redirect_options_block_hop_to_a_host_with_a_private_record(): void
        {
            $this->fakeDns(['rebind.example.test' => ['A' => ['93.184.216.34', '10.0.0.5']]]);
            $onRedirect = OutboundUrlGuard::redirectOptions()['on_redirect'];

            $this->expectException(\RuntimeException::class);

            $onRedirect(new Request('GET', 'https://8.8.8.8/'), new Response(302), new Uri('https://rebind.example.test/'));
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
}
