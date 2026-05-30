<?php

namespace Leantime\Core\Exceptions\Contracts;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Contract for Leantime's first-class domain exceptions.
 *
 * The design principle: exceptions carry *semantics*; each entry point owns *format*.
 * A single thrown exception must render correctly across all of Leantime's surfaces —
 * the JSON-RPC endpoint, the web/HTMX controllers (via the global ExceptionHandler), and
 * any future REST surface — so the exception declares what it *means* and lets each
 * surface decide how to present it:
 *
 *  - getStatusCode()  : the HTTP status for the web/REST surfaces. By extending Symfony's
 *                       HttpExceptionInterface, the global ExceptionHandler honors this with
 *                       no special-casing (its isHttpException() check already keys off it).
 *  - getRpcCode()     : the JSON-RPC 2.0 error code for the /api/jsonrpc surface
 *                       (JsonRpcErrorResponse::fromException reads it).
 *  - getClientMessage(): a message that is safe to expose to a client. getMessage() stays
 *                       internal/loggable; this is the curated, user-facing sentence.
 *  - getErrorData()   : optional structured detail (e.g. a field => [messages] map for
 *                       validation), serialized into the JSON-RPC error `data` member.
 *
 * @see \Leantime\Core\Exceptions\LeantimeException The abstract base implementing this.
 * @see \Leantime\Core\Http\Responses\JsonRpcErrorResponse::fromException()
 */
interface LeantimeExceptionInterface extends HttpExceptionInterface
{
    /**
     * JSON-RPC 2.0 error code for this failure (e.g. -32602 invalid params).
     */
    public function getRpcCode(): int;

    /**
     * A client-safe description of the failure. Distinct from getMessage(), which may
     * contain internal detail that should only be logged.
     */
    public function getClientMessage(): string;

    /**
     * Optional structured detail (e.g. ['field' => ['message', ...]]); empty when none.
     *
     * @return array<string, mixed>
     */
    public function getErrorData(): array;
}
