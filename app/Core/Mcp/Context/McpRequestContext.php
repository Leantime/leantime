<?php

namespace Leantime\Core\Mcp\Context;

use Leantime\Core\Mcp\Auth\McpPrincipal;

class McpRequestContext
{
    public function __construct(
        public readonly string $requestId,
        public readonly mixed $jsonRpcId,
        public readonly string $method,
        public readonly McpPrincipal $principal,
        public readonly ?string $mcpSessionId,
        public readonly string $correlationId,
        public readonly ?string $idempotencyKey,
        public readonly ?string $remoteIp,
        public readonly ?string $userAgent,
        public readonly ?string $protocolVersion = null,
        public readonly ?int $approvedByUserId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'jsonRpcId' => $this->jsonRpcId,
            'method' => $this->method,
            'principalUserId' => $this->principal->userId,
            'principalRoleId' => $this->principal->roleId,
            'principalRole' => $this->principal->role,
            'accessTokenId' => $this->principal->accessTokenId,
            'tokenName' => $this->principal->tokenName,
            'abilities' => $this->principal->abilities,
            'agentId' => $this->principal->agentId,
            'mcpSessionId' => $this->mcpSessionId,
            'correlationId' => $this->correlationId,
            'idempotencyKey' => $this->idempotencyKey,
            'remoteIp' => $this->remoteIp,
            'userAgent' => $this->userAgent,
            'protocolVersion' => $this->protocolVersion,
            'approvedByUserId' => $this->approvedByUserId,
        ];
    }
}
