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

namespace Unit\app\Domain\Notifications\Services {

    use GuzzleHttp\Exception\BadResponseException;
    use GuzzleHttp\Exception\ConnectException;
    use GuzzleHttp\Exception\RequestException;
    use GuzzleHttp\Exception\TransferException;
    use GuzzleHttp\Handler\CurlHandler;
    use GuzzleHttp\Promise\Create;
    use GuzzleHttp\Promise\PromiseInterface;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use Illuminate\Support\Facades\Log;
    use Leantime\Domain\Notifications\Services\Webhooks;
    use Leantime\Domain\Notifications\Services\WebhookTransport;
    use Psr\Http\Message\RequestInterface;
    use Unit\TestCase;

    /**
     * The transport's security contract, asserted at the cURL handler boundary: the request the
     * handler receives must be pinned to the address the SSRF guard validated (CURLOPT_RESOLVE) on a
     * connection of its own (never a pooled one, never left for the next request) while keeping the
     * real hostname for Host/SNI/certificate checks, go through the injected cURL handler
     * only, use no proxy, follow no redirect and verify TLS. DNS is faked (see the namespaced
     * dns_get_record above) and the handler records instead of connecting, so nothing here touches
     * the network.
     */
    class WebhookTransportTest extends TestCase
    {
        private const SECRET_ENDPOINT = 'https://hooks.example.test/hooks/secret-token?sig=secret-sig';

        private const PUBLIC_V4 = '93.184.216.34';

        private const PUBLIC_V6 = '2606:2800:220:1::248';

        /**
         * Hostnames the fake resolver was asked about, in order.
         *
         * @var array<int, string>
         */
        private array $dnsLookups = [];

        /**
         * Requests the fake cURL handler received: ['request' => RequestInterface, 'options' => array].
         *
         * @var array<int, array{request: RequestInterface, options: array<string, mixed>}>
         */
        private array $handledRequests = [];

        /**
         * Environment proxy variables set by a test, restored in tearDown.
         *
         * @var array<string, string|false>
         */
        private array $savedEnvironment = [];

        protected function tearDown(): void
        {
            unset($GLOBALS['leantimeTestDnsResolver']);

            foreach ($this->savedEnvironment as $name => $value) {
                putenv($value === false ? $name : $name.'='.$value);
            }

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

                return $this->dnsRecords($hostname, $type, $records[$hostname] ?? []);
            };
        }

        /**
         * @param  array{A?: array<int, string>, AAAA?: array<int, string>}  $answer
         * @return array<int, array<string, string>> Records shaped like dns_get_record()'s.
         */
        private function dnsRecords(string $hostname, int $type, array $answer): array
        {
            if ($type === DNS_A) {
                return array_map(fn (string $ip) => ['host' => $hostname, 'type' => 'A', 'ip' => $ip], $answer['A'] ?? []);
            }

            if ($type === DNS_AAAA) {
                return array_map(fn (string $ip) => ['host' => $hostname, 'type' => 'AAAA', 'ipv6' => $ip], $answer['AAAA'] ?? []);
            }

            return [];
        }

        /**
         * A transport over a cURL handler that records each request and answers from $outcomes
         * (a Response, or a Throwable to reject with) instead of opening a connection.
         *
         * @param  array<int, Response|\Throwable>  $outcomes
         */
        private function makeTransport(array $outcomes = []): WebhookTransport
        {
            $this->handledRequests = [];
            $record = function (RequestInterface $request, array $options): void {
                $this->handledRequests[] = ['request' => $request, 'options' => $options];
            };

            $recordingCurlHandler = new class($outcomes, $record) extends CurlHandler
            {
                public function __construct(private array $outcomes, private \Closure $record) {}

                public function __invoke(RequestInterface $request, array $options): PromiseInterface
                {
                    ($this->record)($request, $options);
                    $outcome = array_shift($this->outcomes) ?? new Response(204);

                    return $outcome instanceof \Throwable ? Create::rejectionFor($outcome) : Create::promiseFor($outcome);
                }
            };

            return new WebhookTransport($recordingCurlHandler);
        }

        /**
         * @return array<string, mixed>
         */
        private function curlOptions(int $index = 0): array
        {
            return $this->handledRequests[$index]['options']['curl'] ?? [];
        }

        public function test_pins_the_validated_address_and_keeps_the_hostname(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);
            $payload = ['event' => 'notification', 'subject' => 'To-Do updated', 'recipientId' => 7];

            $this->makeTransport([new Response(204)])->post(self::SECRET_ENDPOINT, $payload);

            $this->assertCount(1, $this->handledRequests);
            $request = $this->handledRequests[0]['request'];
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame(self::SECRET_ENDPOINT, (string) $request->getUri(), 'The URL keeps its hostname so SNI and the certificate check use it');
            $this->assertSame('hooks.example.test', $request->getHeaderLine('Host'));
            $this->assertSame(['hooks.example.test:443:'.self::PUBLIC_V4], $this->curlOptions()[CURLOPT_RESOLVE], 'cURL must connect to the validated address, not resolve the host again');
            $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            $this->assertSame($payload, json_decode((string) $request->getBody(), true));
        }

        public function test_pins_an_explicit_port_and_brackets_an_ipv6_address(): void
        {
            $this->fakeDns(['hooks.example.test' => ['AAAA' => [self::PUBLIC_V6]]]);

            $this->makeTransport()->post('https://hooks.example.test:8443/hook', []);

            $this->assertSame(['hooks.example.test:8443:['.self::PUBLIC_V6.']'], $this->curlOptions()[CURLOPT_RESOLVE]);
            $this->assertSame('hooks.example.test:8443', $this->handledRequests[0]['request']->getHeaderLine('Host'));
        }

        public function test_pins_one_of_the_validated_addresses_when_the_host_has_several(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4, '93.184.216.35'], 'AAAA' => [self::PUBLIC_V6]]]);

            $this->makeTransport()->post('https://hooks.example.test/hook', []);

            $this->assertSame(['hooks.example.test:443:'.self::PUBLIC_V4], $this->curlOptions()[CURLOPT_RESOLVE]);
        }

        public function test_pins_the_answer_that_was_validated_even_if_dns_changes_afterwards(): void
        {
            // DNS rebinding: the first answer is public, every later one points inside.
            $answeredOnce = [];
            $this->dnsLookups = [];
            $GLOBALS['leantimeTestDnsResolver'] = function (string $hostname, int $type) use (&$answeredOnce): array {
                $this->dnsLookups[] = $hostname;
                $answer = isset($answeredOnce[$type]) ? ['A' => ['127.0.0.1'], 'AAAA' => ['::1']] : ['A' => [self::PUBLIC_V4]];
                $answeredOnce[$type] = true;

                return $this->dnsRecords($hostname, $type, $answer);
            };

            $this->makeTransport()->post('https://rebind.example.test/hook', []);

            $this->assertSame(['rebind.example.test:443:'.self::PUBLIC_V4], $this->curlOptions()[CURLOPT_RESOLVE]);
            $this->assertSame(['rebind.example.test', 'rebind.example.test'], $this->dnsLookups, 'One A and one AAAA lookup, then no second resolution of the host');
        }

        /**
         * CURLOPT_RESOLVE only steers new connections: cURL hands a pooled live connection to the
         * next request for the same host and port without looking at the pin, so a later delivery
         * would reach the address validated for an earlier one even after DNS moved on. Every
         * request must open its own connection and close it when done.
         */
        public function test_each_delivery_connects_afresh_to_the_address_validated_for_it(): void
        {
            $aLookups = 0;
            $GLOBALS['leantimeTestDnsResolver'] = function (string $hostname, int $type) use (&$aLookups): array {
                if ($type !== DNS_A) {
                    return [];
                }
                $aLookups++;

                return $this->dnsRecords($hostname, $type, ['A' => [$aLookups === 1 ? self::PUBLIC_V4 : '93.184.216.35']]);
            };
            $transport = $this->makeTransport();

            $transport->post(self::SECRET_ENDPOINT, ['delivery' => 1]);
            $transport->post(self::SECRET_ENDPOINT, ['delivery' => 2]);

            $this->assertSame(['hooks.example.test:443:'.self::PUBLIC_V4], $this->curlOptions(0)[CURLOPT_RESOLVE]);
            $this->assertSame(['hooks.example.test:443:93.184.216.35'], $this->curlOptions(1)[CURLOPT_RESOLVE]);
            foreach ([0, 1] as $index) {
                $this->assertTrue($this->curlOptions($index)[CURLOPT_FRESH_CONNECT] ?? false, 'A pooled connection must never carry a request past its pin');
                $this->assertTrue($this->curlOptions($index)[CURLOPT_FORBID_REUSE] ?? false, 'A connection must not outlive the request it was pinned for');
            }
        }

        /**
         * @dataProvider ipLiteralProvider
         */
        public function test_ip_literal_endpoints_are_reached_directly_without_dns(string $url): void
        {
            $this->fakeDns([]);

            $this->makeTransport()->post($url, []);

            $this->assertCount(1, $this->handledRequests);
            $this->assertSame($url, (string) $this->handledRequests[0]['request']->getUri());
            $this->assertArrayNotHasKey(CURLOPT_RESOLVE, $this->curlOptions(), 'cURL never resolves an IP literal, so there is nothing to pin');
            $this->assertTrue($this->curlOptions()[CURLOPT_FRESH_CONNECT] ?? false, 'Every request gets a connection of its own');
            $this->assertTrue($this->curlOptions()[CURLOPT_FORBID_REUSE] ?? false, 'No connection outlives its request');
            $this->assertSame([], $this->dnsLookups);
        }

        public static function ipLiteralProvider(): array
        {
            return [
                'ipv4' => ['https://1.1.1.1/hooks/secret'],
                'ipv6' => ['https://[2606:4700:4700::1111]:8443/hooks/secret'],
            ];
        }

        public function test_uses_the_hardened_request_options(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);

            $this->makeTransport()->post(self::SECRET_ENDPOINT, []);

            $options = $this->handledRequests[0]['options'];
            $this->assertFalse($options['allow_redirects'], 'Redirects are never followed');
            $this->assertTrue($options['verify'], 'TLS peer and host verification stay on');
            $this->assertSame('', $options['proxy'], 'An empty proxy makes cURL connect directly and ignore proxy environment variables');
            $this->assertSame(2, $options['connect_timeout']);
            $this->assertSame(5, $options['timeout']);
        }

        public function test_proxy_environment_variables_are_ignored(): void
        {
            foreach (['HTTP_PROXY', 'HTTPS_PROXY', 'http_proxy', 'https_proxy', 'ALL_PROXY', 'NO_PROXY'] as $name) {
                $this->savedEnvironment[$name] = getenv($name);
            }
            putenv('HTTP_PROXY=http://10.0.0.1:3128');
            putenv('HTTPS_PROXY=http://10.0.0.1:3128');
            putenv('https_proxy=http://10.0.0.1:3128');
            putenv('ALL_PROXY=http://10.0.0.1:3128');
            putenv('NO_PROXY=example.org');
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);

            $this->makeTransport()->post(self::SECRET_ENDPOINT, []);

            $this->assertCount(1, $this->handledRequests, 'The injected cURL handler carries the request — no other handler');
            $this->assertSame('', $this->handledRequests[0]['options']['proxy']);
        }

        /**
         * @dataProvider disallowedDnsProvider
         */
        public function test_hosts_with_any_disallowed_dns_answer_are_never_contacted(array $records): void
        {
            $this->fakeDns(['hooks.example.test' => $records]);
            $transport = $this->makeTransport();

            try {
                $transport->post(self::SECRET_ENDPOINT, ['recipientId' => 7]);
                $this->fail('A host with a non-public address must be refused');
            } catch (\InvalidArgumentException $e) {
                $this->assertSame('Webhook endpoint is not allowed', $e->getMessage());
            }

            $this->assertCount(0, $this->handledRequests);
        }

        public static function disallowedDnsProvider(): array
        {
            return [
                'public and private A' => [['A' => ['93.184.216.34', '10.0.0.5']]],
                'metadata A before public A' => [['A' => ['169.254.169.254', '93.184.216.34']]],
                'public A, loopback AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['::1']]],
                'public A, unique-local AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['fd00::1']]],
                'public A, ipv4-mapped metadata AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['::ffff:169.254.169.254']]],
                'public A, nat64 private AAAA' => [['A' => ['93.184.216.34'], 'AAAA' => ['64:ff9b::a00:5']]],
                'documentation A only' => [['A' => ['203.0.113.5']]],
                'no records' => [[]],
            ];
        }

        /**
         * Host spellings cURL rewrites before resolving (numeric IPv4 forms, percent-encoding),
         * that would not match the pinned name (trailing dot) or that the resolver may expand
         * with its search domains (single-label names), plus embedded credentials. Each is given
         * a public DNS answer so only the transport's own check can stop it.
         *
         * @dataProvider ambiguousHostProvider
         */
        public function test_hosts_curl_could_read_differently_are_never_contacted(string $url, string $resolvedName): void
        {
            $this->fakeDns([$resolvedName => ['A' => [self::PUBLIC_V4]]]);
            $transport = $this->makeTransport();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Webhook endpoint is not allowed');

            try {
                $transport->post($url, []);
            } finally {
                $this->assertCount(0, $this->handledRequests);
            }
        }

        public static function ambiguousHostProvider(): array
        {
            return [
                'shorthand ipv4' => ['https://127.1/hook', '127.1'],
                'hex ipv4' => ['https://0x7f000001/hook', '0x7f000001'],
                'decimal ipv4' => ['https://2130706433/hook', '2130706433'],
                'dotted hex ipv4' => ['https://0x7f.0.0.1/hook', '0x7f.0.0.1'],
                'octal ipv4' => ['https://0177.0.0.1/hook', '0177.0.0.1'],
                'trailing dot' => ['https://hooks.example.test./hook', 'hooks.example.test.'],
                'embedded credentials' => ['https://user:pass@hooks.example.test/hook', 'hooks.example.test'],
                'percent-encoded host' => ['https://hooks%2eexample.test/hook', 'hooks%2eexample.test'],
                'single-label host' => ['https://hooks/hook', 'hooks'],
                'numeric last label' => ['https://hooks.example.123/hook', 'hooks.example.123'],
            ];
        }

        /**
         * @dataProvider disallowedUrlProvider
         */
        public function test_disallowed_urls_are_never_contacted(string $url): void
        {
            $this->fakeDns([]);
            $transport = $this->makeTransport();

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Webhook endpoint is not allowed');

            try {
                $transport->post($url, []);
            } finally {
                $this->assertCount(0, $this->handledRequests);
            }
        }

        public static function disallowedUrlProvider(): array
        {
            return [
                'loopback literal' => ['https://127.0.0.1/hook'],
                'metadata literal' => ['https://169.254.169.254/latest/meta-data'],
                'private ipv6 literal' => ['https://[fd12:3456::1]/hook'],
                'nat64 private ipv6 literal' => ['https://[64:ff9b::a00:1]/hook'],
                'non-http scheme' => ['ftp://1.1.1.1/hook'],
                'plain http' => ['http://1.1.1.1/hook'],
                'malformed' => ['https://:80'],
                'empty' => [''],
            ];
        }

        /**
         * @dataProvider redirectProvider
         */
        public function test_redirects_are_failures_and_never_followed(int $status, string $location): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]], 'next.example.test' => ['A' => ['93.184.216.35']]]);
            $transport = $this->makeTransport([new Response($status, ['Location' => $location]), new Response(200), new Response(200)]);

            try {
                $transport->post(self::SECRET_ENDPOINT, []);
                $this->fail('A '.$status.' answer must be a delivery failure');
            } catch (BadResponseException $e) {
                $this->assertSame($status, $e->getResponse()->getStatusCode());
            }

            $this->assertCount(1, $this->handledRequests, 'The Location is never requested');
        }

        public static function redirectProvider(): array
        {
            return [
                '302 to cloud metadata' => [302, 'http://169.254.169.254/latest/meta-data/'],
                '307 to a private address' => [307, 'https://10.0.0.1/hook'],
                '308 to ipv6 loopback' => [308, 'https://[::1]/hook'],
                '301 to a public host' => [301, 'https://next.example.test/hook'],
                '303 to a public host' => [303, 'https://next.example.test/hook'],
                '300 without location' => [300, ''],
            ];
        }

        /**
         * @dataProvider successStatusProvider
         */
        public function test_2xx_answers_are_delivered(int $status): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);

            $this->makeTransport([new Response($status)])->post(self::SECRET_ENDPOINT, []);

            $this->assertCount(1, $this->handledRequests);
        }

        public static function successStatusProvider(): array
        {
            return ['200' => [200], '201' => [201], '202' => [202], '204' => [204]];
        }

        /**
         * @dataProvider errorStatusProvider
         */
        public function test_error_answers_fail_with_their_status(int $status): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);
            $transport = $this->makeTransport([new Response($status)]);

            try {
                $transport->post(self::SECRET_ENDPOINT, []);
                $this->fail('A '.$status.' answer must be a delivery failure');
            } catch (BadResponseException $e) {
                $this->assertSame($status, $e->getResponse()->getStatusCode());
                $this->assertSame('Webhook endpoint answered with a non-2xx status', $e->getMessage());
            }
        }

        public static function errorStatusProvider(): array
        {
            return ['400' => [400], '404' => [404], '410' => [410], '500' => [500], '503' => [503]];
        }

        public function test_transport_failures_carry_a_fixed_message_without_the_url(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);
            $leakyRequest = new Request('POST', self::SECRET_ENDPOINT);
            $transport = $this->makeTransport([
                new ConnectException('cURL error 28: timed out for '.self::SECRET_ENDPOINT, $leakyRequest),
                new RequestException('cURL error 60: certificate problem for '.self::SECRET_ENDPOINT, $leakyRequest),
            ]);

            try {
                $transport->post(self::SECRET_ENDPOINT, []);
                $this->fail('A connection failure must be reported');
            } catch (ConnectException $e) {
                $this->assertSame('Webhook endpoint could not be reached', $e->getMessage());
                $this->assertNull($e->getPrevious());
            }

            try {
                $transport->post(self::SECRET_ENDPOINT, []);
                $this->fail('A transfer failure must be reported');
            } catch (TransferException $e) {
                $this->assertNotInstanceOf(RequestException::class, $e);
                $this->assertSame('Webhook request failed', $e->getMessage());
                $this->assertNull($e->getPrevious());
            }
        }

        /**
         * The syntax rule is shared with the profile save (Webhooks::isValidEndpointUrl()
         * delegates here), which must never resolve DNS: a hostname's addresses are only
         * checked — and pinned — at delivery time.
         */
        public function test_the_endpoint_syntax_check_never_resolves_dns(): void
        {
            $this->fakeDns(['hooks.example.test' => ['A' => [self::PUBLIC_V4]]]);

            $this->assertTrue(WebhookTransport::isValidEndpointUrl(self::SECRET_ENDPOINT));
            $this->assertTrue(Webhooks::isValidEndpointUrl(self::SECRET_ENDPOINT));
            $this->assertFalse(WebhookTransport::isValidEndpointUrl('https://127.1/hook'));
            $this->assertFalse(Webhooks::isValidEndpointUrl('https://hooks.example.test./hook'));

            $this->assertSame([], $this->dnsLookups, 'Checking a URL never resolves its host');
        }

        /**
         * A per-user token can sit in the endpoint's hostname as well as its path or query, so
         * neither the host nor an address it resolved to may be logged — including by the SSRF
         * guard, the only thing on this path that logs at all.
         */
        public function test_nothing_about_the_endpoint_or_payload_is_logged(): void
        {
            $logged = [];
            foreach (['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'] as $level) {
                Log::shouldReceive($level)->andReturnUsing(function ($message, $context = []) use (&$logged) {
                    $logged[] = $message.' '.json_encode($context, JSON_UNESCAPED_SLASHES);
                });
            }
            $this->fakeDns([
                'secret-token.hooks.example.test' => ['A' => [self::PUBLIC_V4]],
                'secret-token.mixed.example.test' => ['A' => [self::PUBLIC_V4, '10.0.0.5']],
            ]);
            $tokenHostEndpoint = 'https://secret-token.hooks.example.test/hooks/secret-path?sig=secret-sig';
            $transport = $this->makeTransport([
                new Response(500),
                new ConnectException('cURL error 28: timed out for '.$tokenHostEndpoint, new Request('POST', $tokenHostEndpoint)),
            ]);
            $payload = ['message' => 'secret-payload'];

            $urls = [
                $tokenHostEndpoint,                                    // answers 500
                $tokenHostEndpoint,                                    // times out
                'https://secret-token.mixed.example.test/endpoint',    // refused by the guard: one private address
                'https://secret-token.unresolved.example.test/endpoint', // refused by the guard: no address
                'http://secret-token.hooks.example.test/endpoint',     // refused before any lookup: not https
            ];
            foreach ($urls as $url) {
                try {
                    $transport->post($url, $payload);
                } catch (\Throwable) {
                    // Failures are the caller's to log; this test only inspects what was logged.
                }
            }

            $this->assertCount(2, $logged, 'Only the guard logs here, once for each host it refused');
            foreach ($logged as $line) {
                foreach (['secret', 'example.test', self::PUBLIC_V4, '10.0.0.5'] as $partOfTheRequest) {
                    $this->assertStringNotContainsString($partOfTheRequest, $line, 'Webhook host, address, path, query and payload must never reach the logs');
                }
            }
        }
    }
}
