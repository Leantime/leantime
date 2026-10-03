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

    use GuzzleHttp\Exception\TooManyRedirectsException;
    use GuzzleHttp\Handler\CurlHandler;
    use GuzzleHttp\Promise\Create;
    use GuzzleHttp\Promise\PromiseInterface;
    use GuzzleHttp\Psr7\Response;
    use Leantime\Core\Support\OutboundHttpClient;
    use Psr\Http\Message\RequestInterface;
    use Unit\TestCase;

    /**
     * The shared outbound client's security contract, asserted at the cURL handler boundary: every
     * request (and every redirect hop) is pinned to an address the SSRF guard validated for it, on
     * a fresh connection, with no proxy, TLS verification on and bounded timeouts. DNS is faked and
     * the handler records and answers instead of connecting, so nothing touches the network.
     */
    class OutboundHttpClientTest extends TestCase
    {
        private const PUBLIC_V4 = '93.184.216.34';

        private const OTHER_PUBLIC_V4 = '93.184.216.35';

        /**
         * Requests the fake cURL handler received, with their options.
         *
         * @var array<int, array{request: RequestInterface, options: array<string, mixed>}>
         */
        private array $handledRequests = [];

        protected function tearDown(): void
        {
            unset($GLOBALS['leantimeTestDnsResolver']);

            parent::tearDown();
        }

        /**
         * Answers DNS (A records only) from $records instead of the network.
         *
         * @param  array<string, array<int, string>>  $records  host => IPv4 addresses
         */
        private function fakeDns(array $records): void
        {
            $GLOBALS['leantimeTestDnsResolver'] = function (string $hostname, int $type) use ($records): array {
                if ($type !== DNS_A) {
                    return [];
                }

                return array_map(fn (string $ip) => ['host' => $hostname, 'type' => 'A', 'ip' => $ip], $records[$hostname] ?? []);
            };
        }

        /**
         * A client over a cURL handler that records each request and answers from $responses.
         *
         * @param  array<int, Response>  $responses
         */
        private function makeClient(array $responses = []): OutboundHttpClient
        {
            $this->handledRequests = [];
            $record = function (RequestInterface $request, array $options): void {
                $this->handledRequests[] = ['request' => $request, 'options' => $options];
            };

            $handler = new class($responses, $record) extends CurlHandler
            {
                public function __construct(private array $responses, private \Closure $record) {}

                public function __invoke(RequestInterface $request, array $options): PromiseInterface
                {
                    ($this->record)($request, $options);

                    return Create::promiseFor(array_shift($this->responses) ?? new Response(200, [], 'ok'));
                }
            };

            return new OutboundHttpClient($handler);
        }

        public function test_pins_the_validated_address_with_timeouts_and_no_proxy(): void
        {
            $this->fakeDns(['cal.example.test' => [self::PUBLIC_V4]]);

            $response = $this->makeClient()->get('http://cal.example.test/feed.ics');

            $this->assertSame(200, $response->getStatusCode());
            $this->assertCount(1, $this->handledRequests);
            $options = $this->handledRequests[0]['options'];
            $this->assertSame('http://cal.example.test/feed.ics', (string) $this->handledRequests[0]['request']->getUri());
            $this->assertSame(['cal.example.test:80:'.self::PUBLIC_V4], $options['curl'][CURLOPT_RESOLVE]);
            $this->assertTrue($options['curl'][CURLOPT_FRESH_CONNECT]);
            $this->assertTrue($options['curl'][CURLOPT_FORBID_REUSE]);
            $this->assertSame(OutboundHttpClient::DEFAULT_CONNECT_TIMEOUT_SECONDS, $options['connect_timeout']);
            $this->assertSame(OutboundHttpClient::DEFAULT_TOTAL_TIMEOUT_SECONDS, $options['timeout']);
            $this->assertSame('', $options['proxy']);
            $this->assertTrue($options['verify']);
            $this->assertFalse($options['allow_redirects']);
        }

        public function test_caller_can_tune_timeouts_but_not_the_safety_options(): void
        {
            $this->fakeDns(['hooks.example.test' => [self::PUBLIC_V4]]);

            $this->makeClient()->post('https://hooks.example.test/hook', [
                'timeout' => 10,
                'connect_timeout' => 3,
                'proxy' => 'http://proxy.internal:3128',
                'verify' => false,
                'allow_redirects' => true,
                'curl' => [CURLOPT_RESOLVE => ['hooks.example.test:443:127.0.0.1']],
            ]);

            $options = $this->handledRequests[0]['options'];
            $this->assertSame(10, $options['timeout']);
            $this->assertSame(3, $options['connect_timeout']);
            $this->assertSame('', $options['proxy']);
            $this->assertTrue($options['verify']);
            $this->assertFalse($options['allow_redirects']);
            $this->assertSame(['hooks.example.test:443:'.self::PUBLIC_V4], $options['curl'][CURLOPT_RESOLVE]);
        }

        public function test_refuses_a_host_resolving_to_a_private_address_without_connecting(): void
        {
            $this->fakeDns(['internal.example.test' => ['10.0.0.5']]);
            $client = $this->makeClient();

            try {
                $client->get('https://internal.example.test/feed.ics');
                $this->fail('A private destination must be refused');
            } catch (\InvalidArgumentException) {
            }

            $this->assertSame([], $this->handledRequests);
        }

        public function test_refuses_unsafe_urls_without_connecting(): void
        {
            $this->fakeDns(['intranet' => [self::PUBLIC_V4], 'cal.example.test.' => [self::PUBLIC_V4]]);
            $client = $this->makeClient();

            $unsafeUrls = [
                'http://169.254.169.254/latest/meta-data/',
                'http://127.0.0.1/',
                'file:///etc/passwd',
                'gopher://cal.example.test/',
                'http://intranet/feed.ics',           // single label: search-domain expansion
                'http://cal.example.test./feed.ics',  // trailing dot: pin keyed differently
                'http://2130706433/',                 // integer IPv4 spelling
            ];

            foreach ($unsafeUrls as $url) {
                try {
                    $client->get($url);
                    $this->fail('Must refuse '.$url);
                } catch (\InvalidArgumentException) {
                }
            }

            $this->assertSame([], $this->handledRequests);
        }

        public function test_get_follows_a_redirect_and_pins_the_new_host(): void
        {
            $this->fakeDns([
                'cal.example.test' => [self::PUBLIC_V4],
                'cdn.example.test' => [self::OTHER_PUBLIC_V4],
            ]);

            $response = $this->makeClient([
                new Response(302, ['Location' => 'https://cdn.example.test/real.ics']),
                new Response(200, [], 'BEGIN:VCALENDAR'),
            ])->get('https://cal.example.test/feed.ics');

            $this->assertSame('BEGIN:VCALENDAR', (string) $response->getBody());
            $this->assertCount(2, $this->handledRequests);
            $this->assertSame('https://cdn.example.test/real.ics', (string) $this->handledRequests[1]['request']->getUri());
            $this->assertSame(['cdn.example.test:443:'.self::OTHER_PUBLIC_V4], $this->handledRequests[1]['options']['curl'][CURLOPT_RESOLVE]);
        }

        public function test_get_refuses_a_redirect_into_the_internal_network(): void
        {
            $this->fakeDns(['cal.example.test' => [self::PUBLIC_V4]]);
            $client = $this->makeClient([
                new Response(301, ['Location' => 'http://169.254.169.254/latest/meta-data/']),
            ]);

            try {
                $client->get('https://cal.example.test/feed.ics');
                $this->fail('A redirect to an internal address must be refused');
            } catch (\InvalidArgumentException) {
            }

            $this->assertCount(1, $this->handledRequests, 'The internal target must never be contacted');
        }

        public function test_redirect_to_another_host_drops_credentials(): void
        {
            $this->fakeDns([
                'chat.example.test' => [self::PUBLIC_V4],
                'other.example.test' => [self::OTHER_PUBLIC_V4],
            ]);

            $this->makeClient([
                new Response(302, ['Location' => 'https://other.example.test/']),
                new Response(200),
            ])->get('https://chat.example.test/api', ['auth' => ['bot', 'secret']]);

            $this->assertNotSame('', $this->handledRequests[0]['request']->getHeaderLine('Authorization'));
            $this->assertSame('', $this->handledRequests[1]['request']->getHeaderLine('Authorization'));
        }

        public function test_redirect_to_another_origin_on_the_same_host_drops_credentials(): void
        {
            $this->fakeDns(['chat.example.test' => [self::PUBLIC_V4]]);

            // Same host, but https -> http and a different port: a different origin.
            $this->makeClient([
                new Response(302, ['Location' => 'http://chat.example.test:8080/other']),
                new Response(200),
            ])->get('https://chat.example.test/api', ['auth' => ['bot', 'secret']]);

            $this->assertNotSame('', $this->handledRequests[0]['request']->getHeaderLine('Authorization'));
            $this->assertSame('', $this->handledRequests[1]['request']->getHeaderLine('Authorization'));
        }

        public function test_redirect_to_another_host_drops_credential_headers(): void
        {
            $this->fakeDns([
                'chat.example.test' => [self::PUBLIC_V4],
                'other.example.test' => [self::OTHER_PUBLIC_V4],
            ]);

            $this->makeClient([
                new Response(302, ['Location' => 'https://other.example.test/']),
                new Response(200),
            ])->get('https://chat.example.test/api', ['headers' => [
                'authorization' => 'Bearer secret',
                'Proxy-Authorization' => 'Basic x',
                'Cookie' => 'session=1',
                'Accept' => 'text/calendar',
            ]]);

            $second = $this->handledRequests[1]['request'];
            $this->assertSame('', $second->getHeaderLine('Authorization'));
            $this->assertSame('', $second->getHeaderLine('Proxy-Authorization'));
            $this->assertSame('', $second->getHeaderLine('Cookie'));
            $this->assertSame('text/calendar', $second->getHeaderLine('Accept'));
        }

        public function test_same_origin_redirect_keeps_credentials(): void
        {
            $this->fakeDns(['chat.example.test' => [self::PUBLIC_V4]]);

            $this->makeClient([
                new Response(302, ['Location' => '/v2/api']),
                new Response(200),
            ])->get('https://chat.example.test/api', ['auth' => ['bot', 'secret']]);

            $this->assertNotSame('', $this->handledRequests[1]['request']->getHeaderLine('Authorization'));
        }

        public function test_post_does_not_follow_redirects(): void
        {
            $this->fakeDns(['hooks.example.test' => [self::PUBLIC_V4]]);

            $response = $this->makeClient([
                new Response(307, ['Location' => 'https://hooks.example.test/elsewhere']),
            ])->post('https://hooks.example.test/hook', ['body' => '{}']);

            $this->assertSame(307, $response->getStatusCode());
            $this->assertCount(1, $this->handledRequests);
        }

        public function test_gives_up_after_too_many_redirects(): void
        {
            $this->fakeDns(['loop.example.test' => [self::PUBLIC_V4]]);
            $redirects = array_fill(0, OutboundHttpClient::MAX_REDIRECTS + 2, new Response(302, ['Location' => '/again']));

            $this->expectException(TooManyRedirectsException::class);

            $this->makeClient($redirects)->get('https://loop.example.test/start');
        }

        public function test_ip_literal_is_not_pinned_but_still_fresh(): void
        {
            $this->makeClient()->get('https://1.1.1.1/feed.ics');

            $curl = $this->handledRequests[0]['options']['curl'];
            $this->assertArrayNotHasKey(CURLOPT_RESOLVE, $curl);
            $this->assertTrue($curl[CURLOPT_FRESH_CONNECT]);
        }
    }
}
