<?php

namespace Unit\app\Core\Http\Responses;

use Leantime\Core\Http\Responses\Contracts\LeantimeResponseInterface;
use Leantime\Core\Http\Responses\JsonRpcResponse;
use Unit\TestCase;

/**
 * The JSON-RPC 2.0 success envelope response type. Centralizes the `{jsonrpc, result, id}`
 * wire format previously inlined in the Jsonrpc controller.
 */
class JsonRpcResponseTest extends TestCase
{
    public function test_it_is_a_leantime_response(): void
    {
        $this->assertInstanceOf(LeantimeResponseInterface::class, new JsonRpcResponse('x', 1));
    }

    public function test_success_envelope(): void
    {
        $response = (new JsonRpcResponse(['ok' => true], 7))->toResponse(null);
        $body = json_decode($response->getContent(), true);

        $this->assertSame('2.0', $body['jsonrpc']);
        $this->assertSame(['ok' => true], $body['result']);
        $this->assertSame(7, $body['id']);
    }

    public function test_scalar_result_and_string_id_pass_through(): void
    {
        $response = (new JsonRpcResponse(42, 'abc'))->toResponse(null);
        $body = json_decode($response->getContent(), true);

        $this->assertSame(42, $body['result']);
        $this->assertSame('abc', $body['id']);
    }

    public function test_notification_without_id_returns_empty_200(): void
    {
        // A JSON-RPC notification (no id) MUST NOT be responded to.
        $response = (new JsonRpcResponse('ignored', null))->toResponse(null);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }
}
