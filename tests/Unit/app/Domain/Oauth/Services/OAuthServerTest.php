<?php

namespace Unit\app\Domain\Oauth\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Laravel\Passport\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;
use Leantime\Core\Configuration\Environment;
use Leantime\Domain\Oauth\Services\OAuthServer;
use Unit\TestCase;

/**
 * The parts of the OAuth server that decide what clients may do: which redirect URLs can be
 * registered (Claude, ChatGPT, loopback, opt-in extras), what dynamic registration accepts, and the
 * discovery documents MCP clients read first.
 */
class OAuthServerTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function server(string $extraRedirectDomains = '', string $customSchemes = '', ?ClientRepository $clients = null): OAuthServer
    {
        $config = app(Environment::class);
        $config->set('oauthRedirectDomains', $extraRedirectDomains);
        $config->set('oauthCustomSchemes', $customSchemes);

        return new OAuthServer(
            $this->makeEmpty(AuthorizationServer::class),
            $clients ?? $this->makeEmpty(ClientRepository::class),
            $config,
            $this->makeEmpty(OAuthTokenStore::class),
            $this->makeEmpty(CacheRepository::class)
        );
    }

    public function test_claude_and_chatgpt_callbacks_are_allowed(): void
    {
        $server = $this->server();

        $this->assertTrue($server->isAllowedRedirectUri('https://claude.ai/api/mcp/auth_callback'));
        $this->assertTrue($server->isAllowedRedirectUri('https://claude.com/api/mcp/auth_callback'));
        $this->assertTrue($server->isAllowedRedirectUri('https://chatgpt.com/connector_platform_oauth_redirect'));
        $this->assertTrue($server->isAllowedRedirectUri('https://chatgpt.com/connector/oauth/abc123'));
    }

    public function test_loopback_redirects_are_allowed_on_any_port(): void
    {
        $server = $this->server();

        $this->assertTrue($server->isAllowedRedirectUri('http://localhost:6274/oauth/callback'));
        $this->assertTrue($server->isAllowedRedirectUri('http://127.0.0.1:33418/callback'));
        $this->assertTrue($server->isAllowedRedirectUri('http://[::1]:8080/cb'));
    }

    public function test_lookalike_and_unlisted_redirects_are_refused(): void
    {
        $server = $this->server();

        $this->assertFalse($server->isAllowedRedirectUri('https://claude.ai.evil.com/cb'));
        $this->assertFalse($server->isAllowedRedirectUri('https://evil.com/https://claude.ai/'));
        $this->assertFalse($server->isAllowedRedirectUri('https://user:pw@claude.ai/cb'));
        $this->assertFalse($server->isAllowedRedirectUri('https://claude.ai/cb#fragment'));
        $this->assertFalse($server->isAllowedRedirectUri('http://claude.ai/cb'));
        $this->assertFalse($server->isAllowedRedirectUri('http://evil.com/cb'));
        $this->assertFalse($server->isAllowedRedirectUri('javascript:alert(1)'));
        $this->assertFalse($server->isAllowedRedirectUri('cursor://anysphere.cursor-retrieval/oauth/callback'));
    }

    public function test_extra_domains_and_custom_schemes_are_opt_in(): void
    {
        $server = $this->server('https://ai.example.com/, http://insecure.example.com/', 'cursor, javascript, https');

        $this->assertTrue($server->isAllowedRedirectUri('https://ai.example.com/oauth/callback'));
        $this->assertFalse($server->isAllowedRedirectUri('http://insecure.example.com/cb'), 'Only https extras are honoured.');
        $this->assertTrue($server->isAllowedRedirectUri('cursor://anysphere.cursor-retrieval/oauth/callback'));
        $this->assertFalse($server->isAllowedRedirectUri('javascript://x/alert(1)'), 'Dangerous schemes can never be opted in.');
    }

    public function test_registration_refuses_bad_metadata_without_creating_a_client(): void
    {
        $clients = $this->makeEmpty(ClientRepository::class, [
            'createAuthorizationCodeGrantClient' => \Codeception\Stub\Expected::never(),
        ]);
        $server = $this->server(clients: $clients);

        $this->assertSame('invalid_redirect_uri', $server->registerClient([])['body']['error']);
        $this->assertSame('invalid_redirect_uri', $server->registerClient(['redirect_uris' => 'https://claude.ai/cb'])['body']['error']);
        $this->assertSame('invalid_redirect_uri', $server->registerClient(['redirect_uris' => ['https://evil.com/cb']])['body']['error']);
        $this->assertSame('invalid_client_metadata', $server->registerClient([
            'redirect_uris' => ['https://claude.ai/cb'],
            'grant_types' => ['client_credentials'],
        ])['body']['error']);
        $this->assertSame('invalid_client_metadata', $server->registerClient([
            'redirect_uris' => ['https://claude.ai/cb'],
            'token_endpoint_auth_method' => 'client_secret_basic',
        ])['body']['error']);
    }

    public function test_authorization_server_metadata_advertises_what_mcp_clients_need(): void
    {
        $metadata = $this->server()->authorizationServerMetadata();
        $issuer = rtrim(BASE_URL, '/');

        $this->assertSame($issuer, $metadata['issuer']);
        $this->assertSame($issuer.'/oauth/authorize', $metadata['authorization_endpoint']);
        $this->assertSame($issuer.'/oauth/token', $metadata['token_endpoint']);
        $this->assertSame($issuer.'/oauth/register', $metadata['registration_endpoint']);
        $this->assertSame(['S256'], $metadata['code_challenge_methods_supported']);
        $this->assertSame(['none'], $metadata['token_endpoint_auth_methods_supported']);
        $this->assertTrue($metadata['authorization_response_iss_parameter_supported']);
        $this->assertNotContains('client_credentials', $metadata['grant_types_supported']);
    }

    public function test_protected_resource_metadata_describes_mcp_only(): void
    {
        $server = $this->server();
        $issuer = rtrim(BASE_URL, '/');

        $this->assertSame($issuer.'/mcp', $server->protectedResourceMetadata('mcp')['resource']);
        $this->assertSame($issuer.'/mcp', $server->protectedResourceMetadata(null)['resource']);
        $this->assertSame([$issuer], $server->protectedResourceMetadata('mcp')['authorization_servers']);
        $this->assertNull($server->protectedResourceMetadata('api/jsonrpc'));
    }
}
