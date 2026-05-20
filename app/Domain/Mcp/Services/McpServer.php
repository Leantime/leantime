<?php

namespace Leantime\Domain\Mcp\Services;

use Illuminate\Support\Str;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Mcp\Auth\McpAuthenticator;
use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class McpServer
{
    public function __construct(
        private McpAuthenticator $authenticator,
        private ToolRegistry $toolRegistry,
        private ToolExecutor $toolExecutor,
        private McpRepository $mcpRepository,
    ) {}

    public function handle(IncomingRequest $request): Response
    {
        $payload = $this->parsePayload($request);
        $jsonRpcId = $payload['id'] ?? null;
        $requestId = (string) ($request->headers->get('X-Request-Id') ?: Str::uuid());
        $requestLogId = null;

        try {
            $principal = $this->authenticator->authenticate($request);
            $context = new McpRequestContext(
                requestId: $requestId,
                jsonRpcId: $jsonRpcId,
                method: (string) ($payload['method'] ?? 'health'),
                principal: $principal,
                mcpSessionId: $request->headers->get('Mcp-Session-Id'),
                correlationId: (string) ($request->headers->get('X-Correlation-Id') ?: Str::uuid()),
                idempotencyKey: $request->headers->get('Idempotency-Key'),
                remoteIp: $request->getClientIp(),
                userAgent: $request->userAgent(),
                protocolVersion: $payload['params']['protocolVersion'] ?? null,
            );

            $requestLogId = $this->mcpRepository->createRequestLog([
                'requestId' => $requestId,
                'agentId' => $principal->agentId,
                'accessTokenId' => $principal->accessTokenId,
                'userId' => $principal->userId,
                'method' => $context->method,
                'mcpSessionId' => $context->mcpSessionId,
                'correlationId' => $context->correlationId,
                'idempotencyKey' => $context->idempotencyKey,
                'status' => 'received',
                'remoteIp' => $context->remoteIp,
                'userAgent' => $context->userAgent,
                'requestBody' => json_encode($payload),
            ]);

            $result = match ($context->method) {
                '', 'health' => $this->healthPayload(),
                'initialize' => $this->initializePayload(),
                'ping' => ['ok' => true],
                'tools/list' => ['tools' => $this->toolRegistry->definitions()],
                'tools/call' => $this->handleToolCall($context, $payload, $requestLogId),
                'prompts/list' => ['prompts' => []],
                'resources/list' => ['resources' => []],
                'notifications/initialized' => ['ok' => true],
                default => throw new McpException('Method not found', -32601, 404),
            };

            $response = $this->successResponse($result, $jsonRpcId);
            if ($requestLogId !== null) {
                $this->mcpRepository->updateRequestLog($requestLogId, [
                    'status' => 'completed',
                    'httpStatus' => $response->getStatusCode(),
                    'responseBody' => $response->getContent(),
                ]);
            }

            return $response;
        } catch (\Throwable $throwable) {
            $mcpException = $throwable instanceof McpException
                ? $throwable
                : new McpException('Internal MCP server error', -32603, 500);

            $response = $this->errorResponse(
                message: $mcpException->getMessage(),
                id: $jsonRpcId,
                jsonRpcCode: $mcpException->getJsonRpcCode(),
                httpStatus: $mcpException->getHttpStatus(),
                data: $mcpException->getData(),
            );

            if ($requestLogId !== null) {
                $this->mcpRepository->updateRequestLog($requestLogId, [
                    'status' => 'failed',
                    'httpStatus' => $response->getStatusCode(),
                    'errorCode' => (string) $mcpException->getJsonRpcCode(),
                    'responseBody' => $response->getContent(),
                ]);
            }

            return $response;
        }
    }

    private function handleToolCall(McpRequestContext $context, array $payload, int $requestLogId): array
    {
        $params = $payload['params'] ?? [];
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        if ($toolName === '' || ! is_array($arguments)) {
            throw new McpException('tools/call requires a tool name and arguments object', -32602, 400);
        }

        return $this->toolExecutor->execute($context, $toolName, $arguments, $requestLogId);
    }

    private function parsePayload(IncomingRequest $request): array
    {
        if ($request->isMethod('GET')) {
            return ['method' => 'health', 'jsonrpc' => '2.0'];
        }

        $content = trim((string) $request->getContent());
        if ($content === '') {
            return ['method' => 'health', 'jsonrpc' => '2.0'];
        }

        $payload = json_decode($content, true);
        if (! is_array($payload)) {
            throw new McpException('Request body must be valid JSON', -32700, 400);
        }

        return $payload;
    }

    private function healthPayload(): array
    {
        return [
            'serverInfo' => ['name' => 'leantime-mcp', 'version' => '1.0.0'],
            'status' => 'ok',
        ];
    }

    private function initializePayload(): array
    {
        return [
            'protocolVersion' => '2025-03-26',
            'serverInfo' => ['name' => 'leantime-mcp', 'version' => '1.0.0'],
            'capabilities' => [
                'tools' => ['listChanged' => false],
                'prompts' => ['listChanged' => false],
                'resources' => ['listChanged' => false],
            ],
        ];
    }

    private function successResponse(array $result, mixed $id): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ]);
    }

    private function errorResponse(string $message, mixed $id, int $jsonRpcCode, int $httpStatus, mixed $data = null): JsonResponse
    {
        $error = [
            'code' => $jsonRpcCode,
            'message' => $message,
        ];

        if ($data !== null) {
            $error['data'] = $data;
        }

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => $error,
        ], $httpStatus);
    }
}
