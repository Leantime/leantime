<?php

namespace Unit\app\Core\Exceptions;

use Leantime\Core\Exceptions\AuthException;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ExceptionHandler;
use Unit\TestCase;

/**
 * A denied action is an expected 403, not a fault: reporting it put routine permission denials
 * into error tracking next to real crashes. Denials are still logged at info level for audit.
 */
class ExceptionHandlerDontReportTest extends TestCase
{
    public function test_permission_denials_are_not_reported(): void
    {
        $handler = app(ExceptionHandler::class);

        $this->assertFalse($handler->shouldReport(new AuthorizationException));
        $this->assertFalse($handler->shouldReport(new AuthException));
        $this->assertTrue($handler->shouldReport(new \RuntimeException('a real fault')));
    }
}
