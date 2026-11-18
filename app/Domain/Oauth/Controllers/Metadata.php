<?php

namespace Leantime\Domain\Oauth\Controllers;

use Illuminate\Http\JsonResponse;
use Leantime\Domain\Oauth\Services\OAuthServer;

/**
 * OAuth discovery documents (public). MCP clients start here after /mcp answers 401.
 *
 *  - GET /.well-known/oauth-protected-resource[/mcp]   RFC 9728: who protects /mcp
 *  - GET /.well-known/oauth-authorization-server[/…]   RFC 8414: where to authorize, get tokens, register
 */
class Metadata
{
    public function __construct(private OAuthServer $oauthServer) {}

    /**
     * Protected resource metadata. The optional path names the endpoint (/mcp); without it the
     * document describes the only protected endpoint.
     */
    public function protectedResource(?string $path = null): JsonResponse
    {
        $metadata = $this->oauthServer->protectedResourceMetadata($path);

        if ($metadata === null) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        return $this->publicJson($metadata);
    }

    /**
     * Authorization server metadata. Some clients append the resource path; the document is the
     * same either way, since this instance is a single authorization server.
     */
    public function authorizationServer(?string $path = null): JsonResponse
    {
        return $this->publicJson($this->oauthServer->authorizationServerMetadata());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function publicJson(array $data): JsonResponse
    {
        return new JsonResponse($data, 200, ['Cache-Control' => 'public, max-age=3600'], JSON_UNESCAPED_SLASHES);
    }
}
