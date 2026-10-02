<?php

namespace Unit\app\Core\Controller;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Unit\TestCase;

/**
 * `/module/action/{method}` lets the URL pick a controller method. A segment that names an HTTP
 * verb must match the real request method: otherwise a plain link (GET, which browsers send
 * cross-site with SameSite=Lax cookies) could run a state-changing post() handler.
 */
class FrontcontrollerVerbSegmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Exercise the cached-route read path too (only taken with debug off).
        config(['debug' => false]);
    }

    private function frontcontroller(string $method, string $uri = '/tickets/delTicket/post'): Frontcontroller
    {
        // Built by hand: container resolution would pull in the real PermissionEnforcer, which
        // needs a database connection.
        return new Frontcontroller(
            IncomingRequest::create($uri, $method),
            $this->createMock(PermissionEnforcer::class),
        );
    }

    public function test_get_request_cannot_select_post_handler(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->frontcontroller('GET')->getValidControllerCall('tickets', 'delTicket', 'post', 'Controllers');
    }

    public function test_get_request_cannot_select_post_handler_from_route_cache(): void
    {
        // A real POST resolved and cached the route under the "post" key first.
        Cache::store('installation')->set('routes.Tickets.Controllers.DelTicket.post', [
            'class' => \Leantime\Domain\Tickets\Controllers\DelTicket::class,
            'method' => 'post',
        ]);

        $this->expectException(NotFoundHttpException::class);

        $this->frontcontroller('GET')->getValidControllerCall('tickets', 'delTicket', 'post', 'Controllers');
    }

    public function test_verb_segment_is_case_insensitive(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->frontcontroller('GET')->getValidControllerMethod(\Leantime\Domain\Tickets\Controllers\DelTicket::class, 'POST');
    }

    public function test_post_request_may_name_post_segment(): void
    {
        $result = $this->frontcontroller('POST')->getValidControllerCall('tickets', 'delTicket', 'post', 'Controllers');

        $this->assertSame('post', $result['method']);
    }

    public function test_plain_get_request_resolves_get_handler(): void
    {
        $result = $this->frontcontroller('GET', '/tickets/delTicket')->getValidControllerCall('tickets', 'delTicket', 'get', 'Controllers');

        $this->assertSame('get', $result['method']);
    }

    public function test_head_request_may_name_get_segment(): void
    {
        $method = $this->frontcontroller('HEAD')->getValidControllerMethod(\Leantime\Domain\Tickets\Controllers\DelTicket::class, 'get');

        $this->assertSame('get', $method);
    }

    public function test_custom_action_segment_still_resolves(): void
    {
        // Non-verb method names are untouched by the verb check.
        $method = $this->frontcontroller('POST', '/hx/tickets/subtasks/save')
            ->getValidControllerMethod(\Leantime\Domain\Tickets\Hxcontrollers\Subtasks::class, 'save');

        $this->assertSame('save', $method);
    }

    public function test_delete_request_may_name_delete_segment(): void
    {
        // hx-delete="/tickets/subtasks/delete" sends a real DELETE.
        $method = $this->frontcontroller('DELETE', '/hx/tickets/subtasks/delete')
            ->getValidControllerMethod(\Leantime\Domain\Tickets\Hxcontrollers\Subtasks::class, 'delete');

        $this->assertSame('delete', $method);
    }

    public function test_get_request_cannot_select_delete_segment(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->frontcontroller('GET', '/hx/tickets/subtasks/delete')
            ->getValidControllerMethod(\Leantime\Domain\Tickets\Hxcontrollers\Subtasks::class, 'delete');
    }
}
