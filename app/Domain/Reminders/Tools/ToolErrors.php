<?php

namespace Leantime\Domain\Reminders\Tools;

use Laravel\Mcp\Response;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Core\Exceptions\LeantimeException;

/**
 * Turns a refused or invalid reminders call into an error the AI can act on: the validation
 * messages when there are some, otherwise the exception's own message.
 */
trait ToolErrors
{
    private static function errorResponse(LeantimeExceptionInterface $e): Response
    {
        $messages = [];
        if ($e instanceof LeantimeException) {
            foreach ($e->getErrorData() as $errors) {
                foreach ((array) $errors as $message) {
                    $messages[] = (string) $message;
                }
            }
        }

        return Response::error($messages !== [] ? implode(' ', $messages) : $e->getClientMessage());
    }
}
