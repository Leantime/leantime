<?php

namespace Unit\app\Core\Middleware;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Middleware\AuthCheck;
use Leantime\Core\Middleware\AuthenticateSession;

/**
 * AuthCheck records whether an API request was authenticated by a validated token guard or by
 * the web session guard; AuthenticateSession only exempts the former from the password check.
 */
class AuthCheckTokenAttributeTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function authenticateWith(string $passingGuard): ApiRequest
    {
        session(['userdata' => ['id' => 7]]);

        $auth = $this->makeEmpty(AuthFactory::class, [
            'guard' => fn ($name = null) => $this->makeEmpty(Guard::class, ['check' => fn () => $name === $passingGuard]),
            'shouldUse' => fn () => null,
        ]);

        $authCheck = $this->make(AuthCheck::class, ['auth' => $auth]);
        $request = ApiRequest::create('/api/jsonrpc', 'POST', [], [], [], ['HTTP_X_API_KEY' => 'whatever']);

        $result = (fn () => $this->authenticateApi($request, ['leantime', 'sanctum', 'jsonRpc']))->call($authCheck);
        $this->assertTrue($result);

        return $request;
    }

    public function test_api_key_guard_marks_the_request_token_authenticated(): void
    {
        $request = $this->authenticateWith('jsonRpc');

        $this->assertTrue($request->attributes->get(AuthenticateSession::TOKEN_AUTHENTICATED));
    }

    public function test_web_session_guard_does_not_mark_the_request(): void
    {
        $request = $this->authenticateWith('leantime');

        $this->assertFalse($request->attributes->get(AuthenticateSession::TOKEN_AUTHENTICATED));
    }
}
