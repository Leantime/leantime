<?php

namespace Tests\Unit\app\Domain\Oidc\Controllers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Oidc\Controllers\Callback;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regression for #3971: a provider that refuses the authorization request (Authelia without
 * PKCE, an unknown scope, denied consent …) redirects back with `error` instead of `code`.
 * That crashed the callback with "Undefined array key code"; it must end on the login page
 * with the provider's message instead.
 */
class CallbackTest extends \Unit\TestCase
{
    private OidcService $oidc;

    private Template $tpl;

    /** @var array<int, array{0: string, 1: string}> notifications set on the template */
    private array $notifications = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->oidc = $this->createMock(OidcService::class);
        $this->app->instance(OidcService::class, $this->oidc);

        $this->notifications = [];
        $this->tpl = $this->createMock(Template::class);
        $this->tpl->method('setNotification')->willReturnCallback(
            function (string $message, string $type): void {
                $this->notifications[] = [$message, $type];
            }
        );
    }

    private function callbackWith(array $query): Response
    {
        $request = IncomingRequest::create('/oidc/callback', 'GET', $query);
        $this->app->instance(IncomingRequest::class, $request);

        $language = $this->createMock(Language::class);
        $language->method('__')->willReturnCallback(fn (string $key) => match ($key) {
            'oidc.error.providerError' => 'Provider refused (%s)',
            'oidc.error.missingCode' => 'No code came back',
            default => $key,
        });

        $controller = new Callback($request, $this->tpl, $language);

        return $controller->get([]);
    }

    private function assertRedirectsToLogin(Response $response): void
    {
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/auth/login', $response->getTargetUrl());
    }

    public function test_provider_error_is_shown_on_the_login_page(): void
    {
        $this->oidc->expects($this->never())->method('callback');

        $response = $this->callbackWith([
            'error' => 'invalid_request',
            'error_description' => 'Clients must include a code_challenge when performing the authorize code flow, but it is missing.',
            'state' => 'abc',
        ]);

        $this->assertRedirectsToLogin($response);
        $this->assertCount(1, $this->notifications);
        [$message, $type] = $this->notifications[0];
        $this->assertSame('danger', $type);
        $this->assertStringContainsString('Provider refused (invalid_request: Clients must include a code_challenge', $message);
    }

    public function test_provider_error_without_description_still_shows_the_error_code(): void
    {
        $response = $this->callbackWith(['error' => 'access_denied']);

        $this->assertRedirectsToLogin($response);
        $this->assertSame('Provider refused (access_denied)', $this->notifications[0][0]);
    }

    public function test_provider_error_text_is_escaped(): void
    {
        // Anyone can craft this URL and the login page renders notifications as raw HTML.
        $this->callbackWith([
            'error' => 'invalid_request',
            'error_description' => '<script>alert(1)</script>',
        ]);

        $message = $this->notifications[0][0];
        $this->assertStringNotContainsString('<script>', $message);
        $this->assertStringContainsString('&lt;script&gt;', $message);
    }

    public function test_missing_code_without_error_is_a_readable_notification(): void
    {
        $this->oidc->expects($this->never())->method('callback');

        $response = $this->callbackWith(['state' => 'abc']);

        $this->assertRedirectsToLogin($response);
        $this->assertSame([['No code came back', 'danger']], $this->notifications);
    }

    public function test_missing_state_is_a_readable_notification(): void
    {
        $this->oidc->expects($this->never())->method('callback');

        $response = $this->callbackWith(['code' => 'the-code']);

        $this->assertRedirectsToLogin($response);
        $this->assertSame([['No code came back', 'danger']], $this->notifications);
    }

    public function test_code_and_state_are_passed_to_the_service(): void
    {
        $loggedIn = new RedirectResponse('https://leantime.example.com/dashboard/home');
        $this->oidc->expects($this->once())->method('callback')
            ->with('the-code', 'the-state')
            ->willReturn($loggedIn);

        $response = $this->callbackWith(['code' => 'the-code', 'state' => 'the-state']);

        $this->assertSame($loggedIn, $response);
        $this->assertSame([], $this->notifications);
    }

    public function test_service_failure_is_shown_on_the_login_page(): void
    {
        $this->oidc->method('callback')->willThrowException(new \RuntimeException('Failed to retrieve tokens: <b>nope</b>'));

        $response = $this->callbackWith(['code' => 'the-code', 'state' => 'the-state']);

        $this->assertRedirectsToLogin($response);
        $this->assertSame([['Failed to retrieve tokens: &lt;b&gt;nope&lt;/b&gt;', 'danger']], $this->notifications);
    }
}
