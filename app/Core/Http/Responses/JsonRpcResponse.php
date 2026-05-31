<?php

namespace Leantime\Core\Http\Responses;

use Illuminate\Http\JsonResponse;
use Leantime\Core\Http\Responses\Contracts\LeantimeResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The JSON-RPC 2.0 success envelope as a first-class response type.
 *
 * Centralizes the `{jsonrpc, result, id}` wire format (previously inlined in the Jsonrpc
 * controller) in the HTTP layer behind LeantimeResponseInterface, alongside ImageResponse.
 *
 * @see https://www.jsonrpc.org/specification#response_object
 */
class JsonRpcResponse implements LeantimeResponseInterface
{
    /**
     * @param  mixed  $result  The service return value (any JSON value per spec §5).
     * @param  int|string|null  $id  The request id; null indicates a notification.
     */
    public function __construct(
        private mixed $result,
        private int|string|null $id = null,
    ) {}

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        // A request without an id is a JSON-RPC notification and MUST NOT be responded to.
        // @see https://www.jsonrpc.org/specification#notification
        if ($this->id === null) {
            return new Response('', Response::HTTP_OK);
        }

        return new JsonResponse([
            'jsonrpc' => '2.0',
            'result' => $this->result,
            'id' => $this->id,
        ]);
    }
}
