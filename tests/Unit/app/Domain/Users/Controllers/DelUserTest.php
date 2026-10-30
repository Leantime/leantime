<?php

namespace Unit\app\Domain\Users\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Plugins\Plugins;
use Leantime\Core\UI\Template;
use Leantime\Domain\Users\Controllers\DelUser;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * The "Delete user" confirmation page must say which user is about to be
 * deleted (#3851), escaped, and must not show a confirmation for a user that
 * doesn't exist.
 */
class DelUserTest extends TestCase
{
    /**
     * Template names the controller displayed, with their response codes.
     *
     * @var array<int, array{0: string, 1: int}>
     */
    private array $displayed = [];

    private array $originalPost = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        // The view factory discovers plugin composer paths and can() checks the
        // permission engine; both hit the database, which unit tests do not have.
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(Plugins::class, $plugins);

        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('currentUserCan')->willReturn(true);
        app()->instance(PermissionService::class, $permissions);

        $this->originalPost = $_POST;
        $this->displayed = [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;

        parent::tearDown();
    }

    /**
     * Builds the controller around the given user service, rendering the real
     * delete page from whatever the controller assigns.
     */
    private function controller(UserService $userService): DelUser
    {
        app()->instance(UserService::class, $userService);

        $assigned = [];
        $tpl = $this->createMock(Template::class);
        $tpl->method('assign')->willReturnCallback(function (string $name, mixed $value) use (&$assigned): void {
            $assigned[$name] = $value;
        });
        $tpl->method('display')->willReturnCallback(
            function (string $template, string $layout = 'app', int $responseCode = 200) use (&$assigned): Response {
                $this->displayed[] = [$template, $responseCode];

                if ($template !== 'users.delUser') {
                    return new Response('', $responseCode);
                }

                return new Response(view('users::delUser', $this->sharedData() + $assigned)->render(), $responseCode);
            }
        );

        return new DelUser($this->createMock(IncomingRequest::class), $tpl, $this->createMock(Language::class));
    }

    public function test_confirmation_names_the_user_escaped(): void
    {
        $userService = $this->createMock(UserService::class);
        $userService->method('getUser')->with(42)->willReturn([
            'id' => 42,
            'firstname' => 'Ada <script>alert(1)</script>',
            'lastname' => 'Lovelace',
            'username' => 'ada@example.com',
        ]);

        $html = (string) $this->controller($userService)->get(['id' => '42'])->getContent();

        $this->assertSame([['users.delUser', 200]], $this->displayed);
        $this->assertStringContainsString('Ada &lt;script&gt;alert(1)&lt;/script&gt; Lovelace', $html);
        $this->assertStringContainsString('(ada@example.com)', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_confirmation_falls_back_to_the_username_without_a_name(): void
    {
        $userService = $this->createMock(UserService::class);
        $userService->method('getUser')->willReturn([
            'id' => 42,
            'firstname' => '',
            'lastname' => null,
            'username' => 'ada@example.com',
        ]);

        $html = (string) $this->controller($userService)->get(['id' => '42'])->getContent();

        $this->assertStringContainsString('<strong>ada@example.com</strong>', $html);
        $this->assertStringNotContainsString('(ada@example.com)', $html);
    }

    public function test_unknown_user_shows_not_found_instead_of_a_confirmation(): void
    {
        $userService = $this->createMock(UserService::class);
        $userService->method('getUser')->willReturn(false);

        $response = $this->controller($userService)->get(['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([['errors.error404', 404]], $this->displayed);
    }

    public function test_id_zero_is_not_resolved_to_the_logged_in_user(): void
    {
        // The service reads id 0 as "the current user"; the page must not offer to delete them.
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->never())->method('getUser');

        $response = $this->controller($userService)->get(['id' => '0']);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_posting_for_an_unknown_user_deletes_nothing(): void
    {
        session(['formTokenName' => 'formToken', 'formTokenValue' => 'expected-token']);
        $_POST = ['del' => '1', 'formToken' => 'expected-token'];

        $userService = $this->createMock(UserService::class);
        $userService->method('getUser')->willReturn(false);
        $userService->expects($this->never())->method('deleteUser');

        $response = $this->controller($userService)->post(['id' => '999']);

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * Stand-ins for the layout and the globals Template::setupGlobalVars() shares.
     */
    private function sharedData(): array
    {
        $layoutDir = sys_get_temp_dir().'/lt-deluser-layout-'.getmypid();
        if (! is_dir($layoutDir)) {
            mkdir($layoutDir);
        }
        file_put_contents($layoutDir.'/stub.blade.php', '@yield("content") @stack("scripts")');
        app('view')->addNamespace('delusertest', $layoutDir);

        $globals = [
            'tpl' => new class
            {
                public function __call(string $name, array $arguments): string
                {
                    return '';
                }
            },
        ];

        // Shared, not just passed, like setupGlobalVars() does: components (the page
        // header's plugin hooks call $tpl) don't see the page's own variables.
        app('view')->share($globals);

        return ['layout' => 'delusertest::stub'] + $globals;
    }
}
