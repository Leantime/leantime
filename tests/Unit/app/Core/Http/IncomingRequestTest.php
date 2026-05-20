<?php

namespace Tests\Unit\app\Core\Http;

use Leantime\Core\Http\IncomingRequest;

class IncomingRequestTest extends \PHPUnit\Framework\TestCase
{
    public function test_mcp_path_is_treated_as_api_or_cron_request(): void
    {
        $request = IncomingRequest::create('/mcp', 'POST');

        $this->assertTrue($request->isApiRequest());
        $this->assertTrue($request->isApiOrCronRequest());
    }
}
