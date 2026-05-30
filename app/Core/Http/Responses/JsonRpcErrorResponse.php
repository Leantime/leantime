<?php

namespace Leantime\Core\Http\Responses;

use Illuminate\Http\JsonResponse;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Core\Http\Responses\Contracts\LeantimeResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * The JSON-RPC 2.0 error envelope as a first-class response type.
 *
 * Centralizes the `{jsonrpc, error: {code, message, data}, id}` wire format and is the single
 * place that turns a thrown exception into a client-facing JSON-RPC error — without leaking
 * internal detail. A LeantimeException maps to its own code/message/data; any other throwable
 * is reported as a generic server error (the caller is responsible for logging it).
 *
 * Note: JSON-RPC carries the error in-band, so the HTTP status stays 200; real HTTP status
 * codes belong on the web/REST surfaces (handled by the global ExceptionHandler), not here.
 *
 * @see https://www.jsonrpc.org/specification#error_object
 */
class JsonRpcErrorResponse implements LeantimeResponseInterface
{
    public function __construct(
        private int $code,
        private string $message,
        private mixed $data = null,
        private int|string|null $id = 0,
    ) {}

    /**
     * Build an error envelope from a thrown exception.
     *
     * A LeantimeException is trusted to expose a client-safe code, message and data.
     * Anything else is collapsed to a generic server error so internal messages/stack
     * detail never reach the client; log such throwables at the call site.
     */
    public static function fromException(Throwable $e, int|string|null $id = 0): self
    {
        if ($e instanceof LeantimeExceptionInterface) {
            return new self(
                $e->getRpcCode(),
                $e->getClientMessage(),
                $e->getErrorData() ?: null,
                $id,
            );
        }

        return new self(-32000, 'Server error', null, $id);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        return new JsonResponse([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'data' => $this->data,
            ],
            'id' => $this->id,
        ]);
    }
}
