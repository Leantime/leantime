<?php

namespace Unit\app\Core\Http;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Http\RequestTypes\ApiRequestType;

/**
 * Request-type classification must use the same decoded, slash-collapsed path the router
 * dispatches on. Classifying from the raw URI let `/%61pi/jsonrpc` or `/api//jsonrpc` reach
 * the JSON-RPC controller while the middleware treated it as an ordinary cookie-session web
 * request.
 */
class IncomingRequestClassificationTest extends \Unit\TestCase
{
    /**
     * Build a request the way the web server hands it to PHP (raw REQUEST_URI). Request::create()
     * would parse a leading `//` as a scheme-relative host, which a real server never does.
     */
    private function requestFor(string $requestUri, string $method = 'GET', array $headers = []): IncomingRequest
    {
        return new IncomingRequest([], [], [], [], [], array_merge([
            'REQUEST_URI' => $requestUri,
            'REQUEST_METHOD' => $method,
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => APP_ROOT.'/public/index.php',
            'PHP_SELF' => '/index.php',
            'HTTP_HOST' => 'localhost',
        ], $headers));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function jsonRpcPathVariants(): array
    {
        return [
            'canonical' => ['/api/jsonrpc'],
            'query string' => ['/api/jsonrpc?method=x&params=e30='],
            'percent-encoded letter' => ['/%61pi/jsonrpc'],
            'encoded second segment' => ['/api/%6Asonrpc'],
            'duplicate slash' => ['/api//jsonrpc'],
            'leading duplicate slash' => ['//api/jsonrpc'],
            'upper case' => ['/API/JsonRpc'],
            'encoded slash' => ['/api%2Fjsonrpc'],
        ];
    }

    /**
     * @dataProvider jsonRpcPathVariants
     */
    public function test_every_routable_json_rpc_path_is_classified_as_api(string $uri): void
    {
        $request = $this->requestFor($uri);

        $this->assertTrue($request->isApiOrCronRequest(), $uri.' must be classified as an API request');
        $this->assertTrue($request->isApiRequest(), $uri.' must be an API path');
        $this->assertTrue((new ApiRequestType)->matches($request), $uri.' must be detected as an ApiRequest');
    }

    public function test_mcp_paths_are_classified_on_the_normalized_path(): void
    {
        foreach (['/mcp', '/mcp/', '/%6Dcp', '//mcp', '/MCP?x=1'] as $uri) {
            $request = $this->requestFor($uri, 'POST');

            $this->assertTrue($request->isMcpRequest(), $uri.' must be an MCP request');
            $this->assertTrue($request->isApiRequest(), $uri.' must be an API path');
        }

        $this->assertFalse($this->requestFor('/mcpx', 'POST')->isMcpRequest());
    }

    public function test_cron_is_classified_on_the_normalized_path(): void
    {
        $this->assertTrue($this->requestFor('/cron/run')->isApiOrCronRequest());
        $this->assertTrue($this->requestFor('/%63ron/run')->isApiOrCronRequest());
    }

    public function test_web_paths_are_not_api(): void
    {
        foreach (['/', '/tickets/showAll', '/dashboard/home', '/apiary/jsonrpc'] as $uri) {
            $request = $this->requestFor($uri);

            $this->assertFalse($request->isApiOrCronRequest(), $uri);
            $this->assertFalse($request->isMcpRequest(), $uri);
            $this->assertFalse($request->isApiRequest(), $uri);
        }

        // Other /api endpoints are API paths but not JSON-RPC.
        $i18n = $this->requestFor('/api/i18n');
        $this->assertTrue($i18n->isApiRequest());
        $this->assertFalse($i18n->isApiOrCronRequest());
    }

    public function test_non_canonical_paths_are_flagged(): void
    {
        foreach (['/%61pi/jsonrpc', '/api//jsonrpc', '/api%2Fjsonrpc', '/api/jsonrpc//x'] as $uri) {
            $this->assertTrue($this->requestFor($uri)->hasNonCanonicalPath(), $uri);
        }

        foreach (['/api/jsonrpc', '/api/jsonrpc/', '/API/jsonrpc', '/api/jsonrpc?x=%61', '/mcp'] as $uri) {
            $this->assertFalse($this->requestFor($uri)->hasNonCanonicalPath(), $uri);
        }
    }
}
