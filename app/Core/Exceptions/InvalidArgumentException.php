<?php

namespace Leantime\Core\Exceptions;

use Exception;
use Throwable;

class InvalidArgumentException extends Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link https://php.net/manual/en/exception.construct.php
     *
     * @param string         $message  [optional] The Exception message to throw.
     * @param Throwable|null $previous [optional] The previous throwable used for the exception chaining.
     * @param int            $code     [optional] The Exception code.
     */
    public function __construct(string $message = '', int $code = 422)
    {
        parent::__construct($message, $code);
        $this->message = "$message";
        $this->code = $code;
    }
}
