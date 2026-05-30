<?php

namespace Leantime\Core\Exceptions;

use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;

/**
 * Base class for Leantime's first-class domain exceptions.
 *
 * Extends plain \Exception (so existing `catch (\Exception)` / `catch (SpecificException)`
 * sites keep working) and implements LeantimeExceptionInterface, which in turn extends
 * Symfony's HttpExceptionInterface — that single inheritance is what lets the global
 * ExceptionHandler honor getStatusCode()/getHeaders() with no handler changes.
 *
 * Subclasses set $statusCode and $rpcCode (and may carry $errorData / override
 * getClientMessage()). See LeantimeExceptionInterface for the design rationale.
 */
abstract class LeantimeException extends \Exception implements LeantimeExceptionInterface
{
    /**
     * HTTP status for the web/REST surfaces.
     */
    protected int $statusCode = 500;

    /**
     * JSON-RPC 2.0 error code. Defaults to the spec's reserved "Internal error".
     */
    protected int $rpcCode = -32603;

    /**
     * Optional structured detail surfaced in the JSON-RPC error `data` member.
     *
     * @var array<string, mixed>
     */
    protected array $errorData = [];

    /**
     * Response headers to attach (HttpExceptionInterface contract).
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRpcCode(): int
    {
        return $this->rpcCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Client-safe message. Defaults to getMessage(); override to curate what a client sees.
     */
    public function getClientMessage(): string
    {
        return $this->getMessage();
    }
}
