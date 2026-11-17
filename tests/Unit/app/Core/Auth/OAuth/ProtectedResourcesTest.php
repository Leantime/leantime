<?php

namespace Unit\app\Core\Auth\OAuth;

use Leantime\Core\Auth\OAuth\OAuthBearerAuthenticator;
use Leantime\Core\Auth\OAuth\ProtectedResources;
use Leantime\Core\Http\IncomingRequest;
use Unit\TestCase;

/**
 * OAuth tokens are accepted only on the endpoints listed in ProtectedResources (/mcp), and a
 * client's RFC 8707 `resource` must name one of them. Clients send the URL the user typed, so
 * harmless spelling differences must match while anything pointing elsewhere must not.
 */
class ProtectedResourcesTest extends TestCase
{
    private function mcpUrl(): string
    {
        return rtrim(BASE_URL, '/').'/mcp';
    }

    public function test_the_canonical_mcp_url_is_ours(): void
    {
        $this->assertSame($this->mcpUrl(), ProtectedResources::url('mcp'));
        $this->assertSame('mcp', ProtectedResources::pathForResource($this->mcpUrl()));
    }

    public function test_trailing_slash_and_case_of_scheme_and_host_still_match(): void
    {
        $this->assertSame('mcp', ProtectedResources::pathForResource($this->mcpUrl().'/'));

        $parts = parse_url($this->mcpUrl());
        $shouted = strtoupper($parts['scheme']).'://'.strtoupper($parts['host']).(isset($parts['port']) ? ':'.$parts['port'] : '').$parts['path'];
        $this->assertSame('mcp', ProtectedResources::pathForResource($shouted));
    }

    public function test_default_port_spelled_out_still_matches(): void
    {
        $parts = parse_url($this->mcpUrl());

        if (isset($parts['port'])) {
            $this->markTestSkipped('BASE_URL has an explicit port in this environment.');
        }

        $defaultPort = $parts['scheme'] === 'https' ? 443 : 80;
        $withPort = $parts['scheme'].'://'.$parts['host'].':'.$defaultPort.$parts['path'];

        $this->assertSame('mcp', ProtectedResources::pathForResource($withPort));
    }

    public function test_other_hosts_paths_queries_and_fragments_are_not_ours(): void
    {
        $this->assertNull(ProtectedResources::pathForResource('https://elsewhere.example.com/mcp'));
        $this->assertNull(ProtectedResources::pathForResource($this->mcpUrl().'/other'));
        $this->assertNull(ProtectedResources::pathForResource($this->mcpUrl().'?tenant=x'));
        $this->assertNull(ProtectedResources::pathForResource($this->mcpUrl().'#x'));
        $this->assertNull(ProtectedResources::pathForResource(rtrim(BASE_URL, '/').'/api/jsonrpc'));
        $this->assertNull(ProtectedResources::pathForResource(''));
        $this->assertNull(ProtectedResources::pathForResource('not a url'));
    }

    public function test_only_mcp_requests_are_protected_resources(): void
    {
        $this->assertSame('mcp', ProtectedResources::pathForRequest(IncomingRequest::create('/mcp', 'POST')));
        $this->assertNull(ProtectedResources::pathForRequest(IncomingRequest::create('/api/jsonrpc', 'POST')));
        $this->assertNull(ProtectedResources::pathForRequest(IncomingRequest::create('/oauth/token', 'POST')));
    }

    public function test_mcp_requires_the_mcp_scope(): void
    {
        $this->assertSame('mcp:use', ProtectedResources::scopeFor('mcp'));
        $this->assertArrayHasKey('mcp:use', ProtectedResources::scopes());
    }

    public function test_only_jwt_shaped_bearer_values_are_treated_as_oauth_tokens(): void
    {
        $this->assertTrue(OAuthBearerAuthenticator::looksLikeAccessToken('eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.c2ln'));
        $this->assertFalse(OAuthBearerAuthenticator::looksLikeAccessToken('3f9c2a7b1d5e4f60a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3'));
        $this->assertFalse(OAuthBearerAuthenticator::looksLikeAccessToken('lt_user_key'));
        $this->assertFalse(OAuthBearerAuthenticator::looksLikeAccessToken('eyJonly.two'));
    }
}
