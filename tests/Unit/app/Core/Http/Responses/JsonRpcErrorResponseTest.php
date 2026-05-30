<?php

namespace Unit\app\Core\Http\Responses;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Http\Responses\Contracts\LeantimeResponseInterface;
use Leantime\Core\Http\Responses\JsonRpcErrorResponse;
use RuntimeException;
use Unit\TestCase;

/**
 * The JSON-RPC 2.0 error envelope response type and its fromException() bridge — the single
 * place a thrown exception becomes a client-facing error. Typed Leantime exceptions expose
 * their own code/message/data; any other throwable is collapsed to a generic server error so
 * internal detail is never leaked.
 */
class JsonRpcErrorResponseTest extends TestCase
{
    public function test_it_is_a_leantime_response(): void
    {
        $this->assertInstanceOf(LeantimeResponseInterface::class, new JsonRpcErrorResponse(-32000, 'x'));
    }

    public function test_error_envelope(): void
    {
        $response = (new JsonRpcErrorResponse(-32602, 'Invalid params', ['field' => ['bad']], 3))->toResponse(null);
        $body = json_decode($response->getContent(), true);

        $this->assertSame('2.0', $body['jsonrpc']);
        $this->assertSame(-32602, $body['error']['code']);
        $this->assertSame('Invalid params', $body['error']['message']);
        $this->assertSame(['field' => ['bad']], $body['error']['data']);
        $this->assertSame(3, $body['id']);
    }

    public function test_from_validation_exception_maps_code_and_field_errors(): void
    {
        $errors = ['name' => ['Name is required.']];

        $response = JsonRpcErrorResponse::fromException(ValidationException::withMessages($errors), 5)->toResponse(null);
        $body = json_decode($response->getContent(), true);

        $this->assertSame(-32602, $body['error']['code']);
        $this->assertSame($errors, $body['error']['data']);
        $this->assertSame(5, $body['id']);
    }

    public function test_from_authorization_exception_uses_auth_code(): void
    {
        $response = JsonRpcErrorResponse::fromException(new AuthorizationException, 1)->toResponse(null);
        $body = json_decode($response->getContent(), true);

        $this->assertSame(-32001, $body['error']['code']);
    }

    public function test_unknown_throwable_is_generic_and_does_not_leak(): void
    {
        $secret = 'internal-db-dsn-with-password';

        $response = JsonRpcErrorResponse::fromException(new RuntimeException($secret), 9)->toResponse(null);
        $body = json_decode($response->getContent(), true);

        $this->assertSame(-32000, $body['error']['code']);
        $this->assertSame('Server error', $body['error']['message']);
        $this->assertNull($body['error']['data']);
        $this->assertStringNotContainsString($secret, $response->getContent());
    }
}
