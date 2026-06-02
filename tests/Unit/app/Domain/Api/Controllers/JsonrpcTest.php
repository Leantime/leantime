<?php

namespace Tests\Unit\app\Domain\Api\Controllers;

use Leantime\Core\Application;
use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Bootstrap\LoadConfig;
use Leantime\Core\Bootstrap\SetRequestForConsole;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Api\Controllers\Jsonrpc;

/**
 * The controller now builds its envelopes through the JsonRpcResponse / JsonRpcErrorResponse
 * response types, so these tests assert on the actual JSON body of the returned Response
 * (behavior) rather than on the Template::displayJson() call that used to construct it.
 */
class JsonrpcTest extends \Unit\TestCase
{
    private Jsonrpc $controller;

    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(APP_ROOT);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);

        $this->app->boot();
        $this->app['view'] = $this->createMock(\Illuminate\View\Factory::class);
        $this->app['session'] = $this->createMock(\Illuminate\Session\SessionManager::class);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);

        // Jsonrpc::init() now type-hints PermissionEnforcer (resolved via app()->call in the
        // base Controller constructor). Bind a no-op mock so the controller builds without
        // pulling in the full permission engine (PermissionService -> Repository -> Db), which
        // this minimal test container can't resolve. These tests don't exercise authorization.
        $this->app->instance(PermissionEnforcer::class, $this->createMock(PermissionEnforcer::class));

        $this->template = $this->createMock(Template::class);
        $language = $this->createMock(Language::class);
        $this->controller = new Jsonrpc($this->app['request'], $this->template, $language);
        $_SERVER['REQUEST_METHOD'] = 'post';
    }

    private function bodyOf($response): array
    {
        return json_decode($response->getContent(), true);
    }

    public function test_method_string_parsing()
    {
        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 1,
            'jsonrpc' => '2.0',
        ];

        $body = $this->bodyOf($this->controller->post($params));

        $this->assertIsArray($body);
        $this->assertArrayHasKey('jsonrpc', $body);
        $this->assertEquals('2.0', $body['jsonrpc']);
    }

    public function test_invalid_method_string()
    {
        $params = [
            'method' => 'invalid.method.string',
            'params' => ['projectId' => 1],
            'id' => 1,
            'jsonrpc' => '2.0',
        ];

        $body = $this->bodyOf($this->controller->post($params));

        $this->assertArrayHasKey('error', $body);
        $this->assertEquals(-32602, $body['error']['code']);
    }

    public function test_missing_json_rpc_version()
    {
        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 1,
        ];

        $body = $this->bodyOf($this->controller->post($params));

        $this->assertArrayHasKey('error', $body);
        $this->assertEquals(-32600, $body['error']['code']);
    }

    public function test_batch_request()
    {
        $params = [
            [
                'method' => 'leantime.rpc.Comments.pollComments',
                'params' => ['projectId' => 1],
                'id' => 1,
                'jsonrpc' => '2.0',
            ],
            [
                'method' => 'leantime.rpc.Comments.pollComments',
                'params' => ['projectId' => 2],
                'id' => 2,
                'jsonrpc' => '2.0',
            ],
        ];

        $body = $this->bodyOf($this->controller->post($params));

        // The batch response is an array with one envelope per sub-request.
        $this->assertIsArray($body);
        $this->assertCount(2, $body);
    }

    /**
     * The riskiest behavioral change: the service-call catch is now catch(\Throwable) and an
     * UNEXPECTED throwable must be collapsed to a generic -32000 with its message NOT leaked.
     * Driven end-to-end through the controller by rebinding the (@api) Comments service to a
     * stub that throws.
     */
    public function test_service_throwing_unknown_error_is_generic_and_not_leaked()
    {
        $secret = 'super-secret-internal-detail';

        $this->app->bind(
            \Leantime\Domain\Comments\Services\Comments::class,
            fn () => new class($secret)
            {
                public function __construct(private string $secret) {}

                public function pollComments(?int $projectId = null, ?int $moduleId = null): array
                {
                    throw new \RuntimeException($this->secret);
                }
            }
        );

        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 42,
            'jsonrpc' => '2.0',
        ];

        $response = $this->controller->post($params);
        $body = $this->bodyOf($response);

        $this->assertSame(-32000, $body['error']['code']);
        $this->assertSame('Server error', $body['error']['message']);
        $this->assertSame(42, $body['id']);
        $this->assertStringNotContainsString($secret, $response->getContent());
    }

    /**
     * A typed Leantime exception thrown by a service maps to ITS JSON-RPC code (here -32001 for
     * AuthorizationException), not the generic -32000, with the request id preserved.
     */
    public function test_service_throwing_typed_exception_maps_to_its_rpc_code()
    {
        $this->app->bind(
            \Leantime\Domain\Comments\Services\Comments::class,
            fn () => new class
            {
                public function pollComments(?int $projectId = null, ?int $moduleId = null): array
                {
                    throw new \Leantime\Core\Exceptions\AuthorizationException;
                }
            }
        );

        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 7,
            'jsonrpc' => '2.0',
        ];

        $body = $this->bodyOf($this->controller->post($params));

        $this->assertSame(-32001, $body['error']['code']);
        $this->assertSame(7, $body['id']);
    }

    /**
     * A notification (no id) whose service call fails must NOT be responded to — the controller
     * returns an empty 200 instead of a JSON-RPC error envelope (JSON-RPC 2.0).
     */
    public function test_notification_service_failure_returns_empty_200()
    {
        $this->app->bind(
            \Leantime\Domain\Comments\Services\Comments::class,
            fn () => new class
            {
                public function pollComments(?int $projectId = null, ?int $moduleId = null): array
                {
                    throw new \RuntimeException('boom');
                }
            }
        );

        // No 'id' => JSON-RPC notification.
        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'jsonrpc' => '2.0',
        ];

        $response = $this->controller->post($params);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    /**
     * @api detection must only recognize the tag at the START of a docblock line (" * @api").
     * A method whose docblock merely MENTIONS @api in prose (e.g. a de-@api'd internal helper
     * documented as "not exposed, unlike @api methods") must NOT become JSON-RPC reachable —
     * regression guard for the IDOR fix where "Not @api:" still matched the old /@api\b/ regex.
     */
    public function test_api_detection_requires_the_tag_at_a_docblock_line_start(): void
    {
        $isApiMethod = new \ReflectionMethod(Jsonrpc::class, 'isApiMethod');
        $isApiMethod->setAccessible(true);
        $invoke = fn (string $class, string $method): bool => $isApiMethod->invoke($this->controller, $class, $method);

        // A genuine ` * @api` docblock line IS recognized.
        $this->assertTrue($invoke(IsApiFixture::class, 'realApiMethod'));
        // A prose mention of @api (and a method with no docblock) must NOT be recognized.
        $this->assertFalse($invoke(IsApiFixture::class, 'proseMentionMethod'));
        $this->assertFalse($invoke(IsApiFixture::class, 'noDocblockMethod'));

        // The real de-@api'd internal helpers must NOT be JSON-RPC reachable (IDOR fixes):
        $this->assertFalse($invoke(\Leantime\Domain\Clients\Services\Clients::class, 'getUserClients'));
        $this->assertFalse($invoke(\Leantime\Domain\Users\Services\Users::class, 'setProfilePicture'));
        $this->assertFalse($invoke(\Leantime\Domain\Users\Services\Users::class, 'editOwn'));
        // ...while a genuine @api service method stays reachable.
        $this->assertTrue($invoke(\Leantime\Domain\Clients\Services\Clients::class, 'getAll'));
    }
}

/**
 * Fixture for isApiMethod() docblock-detection tests.
 */
class IsApiFixture
{
    /**
     * @api
     */
    public function realApiMethod(): void {}

    /**
     * @internal Not exposed over JSON-RPC, unlike @api methods — a prose mention only.
     */
    public function proseMentionMethod(): void {}

    public function noDocblockMethod(): void {}
}
