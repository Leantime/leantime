<?php

namespace Leantime\Core\Mcp;

use RuntimeException;

class McpException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $jsonRpcCode = -32000,
        private int $httpStatus = 400,
        private mixed $data = null,
    ) {
        parent::__construct($message, $jsonRpcCode);
    }

    public function getJsonRpcCode(): int
    {
        return $this->jsonRpcCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
