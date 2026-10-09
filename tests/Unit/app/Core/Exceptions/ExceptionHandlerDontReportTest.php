<?php

namespace Unit\app\Core\Exceptions;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Exceptions\AuthException;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ExceptionHandler;
use Unit\TestCase;

/**
 * A denied action is an expected 403, not a fault: reporting it put routine permission denials
 * into error tracking next to real crashes. It is logged at info level instead, as an audit entry
 * — not every denial passes through PermissionService/PermissionEnforcer, which log their own.
 */
class ExceptionHandlerDontReportTest extends TestCase
{
    public function test_permission_denials_are_logged_for_audit_not_as_errors(): void
    {
        $infoMessages = [];
        Log::shouldReceive('info')->andReturnUsing(function ($message) use (&$infoMessages) {
            $infoMessages[] = $message;
        });
        Log::shouldReceive('error')->never();
        Log::shouldReceive('log')->never();

        $handler = app(ExceptionHandler::class);
        $handler->report(new AuthorizationException('You cannot assign a role higher than your own.'));
        $handler->report(new AuthException);

        $this->assertCount(2, $infoMessages);
        $this->assertStringContainsString('You cannot assign a role higher than your own.', $infoMessages[0]);
    }
}
